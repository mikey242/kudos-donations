#!/usr/bin/env node
/**
 * Manage Kudos Donations translations on translate.wordpress.org.
 *
 *   node bin/i18n.mjs status            Open work per locale, with review links
 *   node bin/i18n.mjs pull [locale...]  Download open strings into .i18n/<locale>.po
 *   node bin/i18n.mjs check [locale...] Validate .i18n/*.po before importing
 *
 * Options: --branch=dev   use the Development (trunk) project instead of Stable
 *          --force        pull: overwrite an existing .i18n/<locale>.po
 *
 * "Open" means untranslated, fuzzy, or waiting (someone else's unapproved
 * suggestion). Pulled files contain only open strings. status also counts
 * "changes requested" (translations a reviewer sent back); pull doesn't fetch
 * those, so handle them on the site. Anything not translated by you is left fuzzy: a prefill borrowed from a sibling locale
 * (fr_BE <-> fr_FR, nl_NL <-> nl_BE), the existing fuzzy text, or the pending
 * suggestion. Translate/review, remove the `#, fuzzy` flag from what you
 * accept, run `check`, then upload the .i18n/<locale>.import.po it writes
 * (reviewed, non-fuzzy strings only) at the import URL it prints.
 * After importing, delete .i18n/<locale>.po before the next pull.
 */
import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const OUT_DIR = join( ROOT, '.i18n' );
const PROJECT = 'wp-plugins/kudos-donations';

// wp_locale => GlotPress path + locales to borrow translations from, in order.
const LOCALES = {
	nl_NL: { path: 'nl/default', siblings: [ 'nl_BE' ] },
	nl_NL_formal: { path: 'nl/formal', siblings: [] }, // je/u differ: no prefill
	nl_BE: { path: 'nl-be/default', siblings: [ 'nl_NL' ] },
	fr_FR: { path: 'fr/default', siblings: [ 'fr_BE' ] },
	fr_BE: { path: 'fr-be/default', siblings: [ 'fr_FR' ] },
};

const args = process.argv.slice( 2 );
const branch =
	args.find( ( a ) => a.startsWith( '--branch=' ) )?.split( '=' )[ 1 ] ??
	'stable';
const [ command, ...rest ] = args.filter( ( a ) => ! a.startsWith( '--' ) );
const selected = rest.length ? rest : Object.keys( LOCALES );

for ( const l of selected ) {
	if ( ! LOCALES[ l ] ) {
		fail( `Unknown locale "${ l }". Known: ${ Object.keys( LOCALES ).join( ', ' ) }` );
	}
}

const base = ( locale ) =>
	`https://translate.wordpress.org/projects/${ PROJECT }/${ branch }/${ LOCALES[ locale ].path }`;

// ---------------------------------------------------------------- commands

async function status() {
	console.log( `Branch: ${ branch }\n` );
	const { translation_sets: sets = [] } = JSON.parse(
		await get( `https://translate.wordpress.org/api/projects/${ PROJECT }/${ branch }/` )
	);
	const rows = [];
	for ( const locale of selected ) {
		const set = sets.find( ( s ) => s.wp_locale === locale );
		rows.push( {
			locale,
			untranslated: set?.untranslated_count ?? '?',
			fuzzy: set?.fuzzy_count ?? '?',
			waiting: set?.waiting_count ?? '?',
			// The API doesn't count these, not even as untranslated.
			'changes requested': await countStatus( locale, 'changesrequested' ),
		} );
	}
	console.table( rows );
	for ( const { locale, waiting, fuzzy, 'changes requested': changes } of rows ) {
		if ( waiting > 0 || fuzzy > 0 ) {
			console.log( `${ locale }: review on site → ${ base( locale ) }/?filters%5Bstatus%5D=waiting_or_fuzzy` );
		}
		if ( changes > 0 ) {
			console.log( `${ locale }: changes requested → ${ base( locale ) }/?filters%5Bstatus%5D=changesrequested` );
		}
	}
}

async function pull() {
	mkdirSync( OUT_DIR, { recursive: true } );

	// Current translations for every locale that might serve as a sibling.
	const current = {};
	const needed = new Set( selected.flatMap( ( l ) => LOCALES[ l ].siblings ) );
	for ( const l of needed ) {
		current[ l ] = new Map(
			parsePo( await exportPo( l, 'current' ) )
				.entries.filter( isTranslated )
				.map( ( e ) => [ key( e ), e ] )
		);
	}

	for ( const locale of selected ) {
		const file = join( OUT_DIR, `${ locale }.po` );
		if ( existsSync( file ) && ! args.includes( '--force' ) ) {
			console.log( `${ locale }: ${ file } exists, skipping (run check/import first, or --force)` );
			continue;
		}

		const untranslated = parsePo( await exportPo( locale, 'untranslated' ) );
		const fuzzy = parsePo( await exportPo( locale, 'fuzzy' ) ).entries;
		const waiting = parsePo( await exportPo( locale, 'waiting' ) ).entries;

		const out = new Map();
		const tally = { empty: 0, prefilled: 0, fuzzy: 0, waiting: 0 };

		for ( const e of untranslated.entries ) {
			const sibling = LOCALES[ locale ].siblings.find( ( s ) => current[ s ]?.has( key( e ) ) );
			const donor = sibling && current[ sibling ].get( key( e ) );
			if ( donor && donor.msgstr.length === e.msgstr.length ) {
				e.msgstr = [ ...donor.msgstr ];
				markFuzzy( e, `prefilled from ${ sibling }` );
				tally.prefilled++;
			} else {
				tally.empty++;
			}
			out.set( key( e ), e );
		}
		for ( const e of fuzzy ) {
			markFuzzy( e, 'fuzzy on WordPress.org' );
			out.set( key( e ), e );
			tally.fuzzy++;
		}
		for ( const e of waiting ) {
			markFuzzy( e, 'pending suggestion on WordPress.org — approve there, or edit here to replace it' );
			// A string can have several suggestions; keep the first, note the others.
			const prev = out.get( key( e ) );
			if ( prev ) {
				prev.comments.push( `# also suggested: ${ e.msgstr.join( ' | ' ) }` );
				continue;
			}
			out.set( key( e ), e );
			tally.waiting++;
		}

		writeFileSync( file, serializePo( { header: untranslated.header, entries: [ ...out.values() ] } ) );
		console.log(
			`${ locale }: ${ out.size } open → ${ file }  (${ tally.empty } empty, ${ tally.prefilled } prefilled, ${ tally.fuzzy } fuzzy, ${ tally.waiting } waiting)`
		);
	}
}

async function check() {
	let errors = 0;
	let msgfmtMissing = false;
	for ( const locale of selected ) {
		const file = join( OUT_DIR, `${ locale }.po` );
		if ( ! existsSync( file ) ) {
			continue;
		}
		const problems = [];
		const { header, entries } = parsePo( readFileSync( file, 'utf8' ) );

		try {
			execFileSync( 'msgfmt', [ '--check', '-o', '/dev/null', file ], { stdio: 'pipe' } );
		} catch ( e ) {
			if ( e.code === 'ENOENT' ) {
				msgfmtMissing = true;
			} else {
				problems.push( [ 'error', `msgfmt: ${ e.stderr?.toString().trim() }` ] );
			}
		}

		let ready = 0, fuzzyCount = 0, empty = 0;
		for ( const e of entries ) {
			if ( ! isTranslated( e ) ) {
				empty++;
				continue;
			}
			if ( e.flags.includes( 'fuzzy' ) ) {
				fuzzyCount++;
			} else {
				ready++;
			}
			const label = JSON.stringify( e.msgid ).slice( 0, 70 );
			e.msgstr.forEach( ( str, i ) => {
				if ( ! str ) {
					problems.push( [ 'error', `${ label }: plural form [${ i }] is empty` ] );
					return;
				}
				const src = i === 0 || e.msgidPlural === undefined ? e.msgid : e.msgidPlural;
				// The singular form may legitimately drop the number ("Un don").
				const plural = e.msgidPlural !== undefined;
				const phA = placeholders( src ), phB = placeholders( str );
				if ( phA.join() !== phB.join() ) {
					problems.push( [ plural ? 'warn' : 'error', `${ label }${ plural ? `[${ i }]` : '' }: placeholders ${ phA.join( ' ' ) || '(none)' } → ${ phB.join( ' ' ) || '(none)' }` ] );
				}
				const tA = tags( src ), tB = tags( str );
				if ( tA.join() !== tB.join() ) {
					problems.push( [ 'error', `${ label }: HTML tags ${ tA.join( ' ' ) || '(none)' } → ${ tB.join( ' ' ) || '(none)' }` ] );
				}
				if ( /^\s/.test( src ) !== /^\s/.test( str ) || /\s$/.test( src ) !== /\s$/.test( str ) ) {
					problems.push( [ 'warn', `${ label }: leading/trailing whitespace differs` ] );
				}
			} );
		}

		console.log( `\n${ locale }: ${ ready } reviewed, ${ fuzzyCount } still fuzzy, ${ empty } empty (fuzzy and empty are not uploaded)` );
		let localeErrors = 0;
		for ( const [ level, msg ] of problems ) {
			console.log( `  ${ level === 'error' ? '✗' : '!' } ${ msg }` );
			localeErrors += level === 'error';
		}
		errors += localeErrors;

		// Only reviewed strings go up: re-uploading prefills and other people's
		// suggestions would just add duplicate fuzzy entries on WordPress.org.
		// Remove any stale import file so a failed check can't be uploaded.
		const importFile = join( OUT_DIR, `${ locale }.import.po` );
		rmSync( importFile, { force: true } );
		if ( ready && ! localeErrors ) {
			const reviewed = entries.filter( ( e ) => isTranslated( e ) && ! e.flags.includes( 'fuzzy' ) );
			writeFileSync( importFile, serializePo( { header, entries: reviewed } ) );
			console.log( `  Upload ${ importFile }\n  at ${ base( locale ) }/import-translations/` );
		}
	}
	if ( msgfmtMissing ) {
		console.log( '\n! msgfmt not found (install gettext); skipped its syntax check.' );
	}
	if ( errors ) {
		fail( `\n${ errors } error(s). Fix them before importing.` );
	}
}

// ---------------------------------------------------------------- helpers

async function get( url ) {
	for ( let attempt = 1; ; attempt++ ) {
		const res = await fetch( url );
		if ( res.ok ) {
			return res.text();
		}
		if ( ( res.status !== 429 && res.status < 500 ) || attempt === 6 ) {
			fail( `${ res.status } fetching ${ url }` );
		}
		// translate.wordpress.org rate-limits bursts of exports.
		const wait = Number( res.headers.get( 'retry-after' ) ) || 5 * 2 ** attempt;
		process.stderr.write( `  rate limited, retrying in ${ wait }s…\n` );
		await new Promise( ( r ) => setTimeout( r, wait * 1000 ) );
	}
}

async function countStatus( locale, status ) {
	const data = JSON.parse(
		await get( `${ base( locale ) }/export-translations/?format=json&filters%5Bstatus%5D=${ status }` )
	);
	return Array.isArray( data ) ? 0 : Object.keys( data ).filter( Boolean ).length;
}

const exportPo = ( locale, status ) =>
	get( `${ base( locale ) }/export-translations/?format=po&filters%5Bstatus%5D=${ status }` );

const key = ( e ) => `${ e.msgctxt ?? '' }\u0004${ e.msgid }`;
const isTranslated = ( e ) => e.msgstr.some( ( s ) => s !== '' );

function markFuzzy( e, note ) {
	if ( ! e.flags.includes( 'fuzzy' ) ) {
		e.flags.push( 'fuzzy' );
	}
	e.comments.push( `# ${ note }` );
}

function placeholders( s ) {
	return ( s.match( /%(?:\d+\$)?[-+ 0#']*\d*(?:\.\d+)?[bcdeEfFgGosuxX%]/g ) ?? [] ).sort();
}

function tags( s ) {
	return ( s.match( /<\/?[a-z][a-z0-9]*/gi ) ?? [] ).map( ( t ) => t.toLowerCase() ).sort();
}

function parsePo( text ) {
	const entries = [];
	let header = null;
	for ( const block of text.replace( /\r\n/g, '\n' ).split( /\n{2,}/ ) ) {
		if ( ! block.trim() ) {
			continue;
		}
		const e = { comments: [], flags: [], msgstr: [] };
		let field = null;
		for ( const line of block.split( '\n' ) ) {
			if ( line.startsWith( '#,' ) ) {
				e.flags.push( ...line.slice( 2 ).split( ',' ).map( ( f ) => f.trim() ).filter( Boolean ) );
			} else if ( line.startsWith( '#' ) ) {
				e.comments.push( line );
			} else {
				const m = line.match( /^(msgctxt|msgid_plural|msgid|msgstr(?:\[(\d+)\])?)\s+(".*")$/ );
				const value = unquote( m ? m[ 3 ] : line.trim() );
				if ( m ) {
					field = m[ 1 ].startsWith( 'msgstr' ) ? [ 'msgstr', Number( m[ 2 ] ?? 0 ) ] : m[ 1 ];
					if ( Array.isArray( field ) ) {
						e.msgstr[ field[ 1 ] ] = value;
					} else {
						e[ field === 'msgid_plural' ? 'msgidPlural' : field ] = value;
					}
				} else if ( Array.isArray( field ) ) {
					e.msgstr[ field[ 1 ] ] += value;
				} else if ( field ) {
					e[ field === 'msgid_plural' ? 'msgidPlural' : field ] += value;
				}
			}
		}
		if ( e.msgid === undefined ) {
			continue;
		}
		if ( e.msgid === '' && e.msgctxt === undefined ) {
			header = e;
		} else {
			entries.push( e );
		}
	}
	return { header, entries };
}

function serializePo( { header, entries } ) {
	const q = ( s ) =>
		'"' + s.replace( /\\/g, '\\\\' ).replace( /"/g, '\\"' ).replace( /\n/g, '\\n' ).replace( /\t/g, '\\t' ) + '"';
	const one = ( e ) => {
		const lines = [ ...e.comments ];
		if ( e.flags.length ) {
			lines.push( `#, ${ e.flags.join( ', ' ) }` );
		}
		if ( e.msgctxt !== undefined ) {
			lines.push( `msgctxt ${ q( e.msgctxt ) }` );
		}
		if ( e === header ) {
			// Keep the header's multi-line layout for readability.
			lines.push( 'msgid ""', 'msgstr ""', ...e.msgstr[ 0 ].split( /(?<=\n)/ ).map( q ) );
			return lines.join( '\n' );
		}
		lines.push( `msgid ${ q( e.msgid ) }` );
		if ( e.msgidPlural !== undefined ) {
			lines.push( `msgid_plural ${ q( e.msgidPlural ) }` );
			e.msgstr.forEach( ( s, i ) => lines.push( `msgstr[${ i }] ${ q( s ?? '' ) }` ) );
		} else {
			lines.push( `msgstr ${ q( e.msgstr[ 0 ] ?? '' ) }` );
		}
		return lines.join( '\n' );
	};
	return [ header, ...entries ].filter( Boolean ).map( one ).join( '\n\n' ) + '\n';
}

function unquote( s ) {
	return s
		.slice( 1, -1 )
		.replace( /\\(.)/g, ( _, c ) => ( { n: '\n', t: '\t', r: '\r' }[ c ] ?? c ) );
}

function fail( msg ) {
	console.error( msg );
	process.exit( 1 );
}

const commands = { status, pull, check };
if ( ! commands[ command ] ) {
	console.log( readFileSync( fileURLToPath( import.meta.url ), 'utf8' ).match( /\/\*\*([\s\S]*?)\*\//)[ 1 ].replace( /^ \* ?/gm, '' ) );
	process.exit( command ? 1 : 0 );
}
await commands[ command ]();
