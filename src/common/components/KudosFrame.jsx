import {useRef} from "@wordpress/element"
import Frame from "react-frame-component"

const KudosFrame = (props) => {
    const iframeRef = useRef()

    const getRootStyles = () => {
        return '' +
            ':root {' +
                '--kudos-theme-primary:' + props.color.primary + ';' +
                '--kudos-theme-primary-dark:' + props.color.primaryDark +
            '}' +
            'body {' +
                'margin:0' +
            '}'
    }

    return (
        <Frame
            ref={iframeRef}
            contentDidMount={() => {
                iframeRef.current.node.style.height = (iframeRef.current.node.contentWindow.document.body.scrollHeight) + 'px'
            }}
            // onLoad={console.log(iframeRef)}
            style={{border: 'none', display: 'block', width: '100%'}}
            initialContent={`<!DOCTYPE html><html><head><style>${getRootStyles()}</style><link rel="stylesheet"
                              href="/wp-content/plugins/kudos-donations/dist/public/kudos-public.css"></head><body><div></div></body></html>`}
        >
            {props.children}
        </Frame>
    )
}

export default KudosFrame