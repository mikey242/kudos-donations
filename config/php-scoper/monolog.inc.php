<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for monolog/monolog.
 *
 * Only the classes actually used by this plugin (configured in config/services.php)
 * and their transitive internal dependencies are included. This reduces the scoped
 * output from ~114 files to ~26 files.
 *
 * Handlers:  RotatingFileHandler, FingersCrossedHandler, StreamHandler (tests only)
 * Formatter: JsonFormatter
 * Processors: PsrLogMessageProcessor, IntrospectionProcessor
 */
return [
	'finders'  => [
		// Core Logger classes.
		Finder::create()
			->files()
			->depth( '== 0' )
			->in( 'vendor/monolog/monolog/src/Monolog' )
			->name(
				[
					'Logger.php',
					'LogRecord.php',
					'DateTimeImmutable.php',
					'Utils.php',
					'ResettableInterface.php',
				]
			),

		// Formatters: JsonFormatter extends NormalizerFormatter; LineFormatter is the
		// default formatter returned by AbstractProcessingHandler::getDefaultFormatter().
		Finder::create()
			->files()
			->depth( '== 0' )
			->in( 'vendor/monolog/monolog/src/Monolog/Formatter' )
			->name(
				[
					'FormatterInterface.php',
					'NormalizerFormatter.php',
					'JsonFormatter.php',
					'LineFormatter.php',
				]
			),

		// Handler base classes and the three concrete handlers we use.
		Finder::create()
			->files()
			->depth( '== 0' )
			->in( 'vendor/monolog/monolog/src/Monolog/Handler' )
			->name(
				[
					'HandlerInterface.php',
					'Handler.php',
					'AbstractHandler.php',
					'AbstractProcessingHandler.php',
					'FormattableHandlerInterface.php',
					'FormattableHandlerTrait.php',
					'ProcessableHandlerInterface.php',
					'ProcessableHandlerTrait.php',
					'StreamHandler.php',
					'RotatingFileHandler.php',
					'FingersCrossedHandler.php',
				]
			),

		// Activation strategy required by FingersCrossedHandler (used when passing
		// an integer log level, which our services.php does via Logger::ERROR).
		Finder::create()
			->files()
			->depth( '== 0' )
			->in( 'vendor/monolog/monolog/src/Monolog/Handler/FingersCrossed' )
			->name(
				[
					'ActivationStrategyInterface.php',
					'ErrorLevelActivationStrategy.php',
				]
			),

		// Processors wired in services.php.
		Finder::create()
			->files()
			->depth( '== 0' )
			->in( 'vendor/monolog/monolog/src/Monolog/Processor' )
			->name(
				[
					'ProcessorInterface.php',
					'PsrLogMessageProcessor.php',
					'IntrospectionProcessor.php',
				]
			),
	],
	'patchers' => [],
];
