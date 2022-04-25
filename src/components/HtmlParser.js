import React from 'react';
import HtmlToReact from 'html-to-react';
import ReactDOMServer from 'react-dom/server';
import Md5 from 'md5';
import LightboxImage from './LightboxImage';
import useUserToken from '../hooks/useUserTokens';

export default function HtmlParser({content}) {
    const {clientId, accessToken} = useUserToken();
    const HtmlToReactParser = HtmlToReact.Parser;
    const processNodeDefinitions = new HtmlToReact.ProcessNodeDefinitions(React);
    const processingInstructions = [
        {
            shouldProcessNode: node => node.name && node.name === "blockquote",
            processNode: (node, children) => {
                const id = Md5(ReactDOMServer.renderToString(children)).substr(2,9);
                const typeRegex = new RegExp("^\\:([a-z]*)", "i")
                const component = children.find(child => child !== "\n");
                let text;
                let type;

                if(typeof component.props.children === "string") {
                    text = component.props.children;
                } else {
                    text = component.props.children.find(child => typeof child === "string");
                }

                const match = text.match(typeRegex);

                if(match != null) {
                    type = match[1];
                } else {
                    type = "info";
                }

                let callout;

                switch(type) {
                    case "warn":
                    case "warning":
                        callout = {
                            title : "Warning",
                            icon : "exclamation-triangle",
                            class : "warning"
                        }
                    break;

                    case "success":
                    case "tip":
                        callout = {
                            title : "Tip",
                            icon : "thumbs-up",
                            class : "success"
                        }
                    break;

                    case "danger":
                        callout = {
                            title : "Danger",
                            icon : "exclamation-circle",
                            class : "danger"
                        }
                    break;

                    case "info":
                    case "note":
                    default:
                        callout = {
                            title : "Note",
                            icon : "info-circle",
                            class : "info"
                        }
                    break;
                }

                return (
                    <div className={`callout-block callout-block-${callout.class}`} key={`callout-${id}`}>
                        <div className="content">
                            <h4 className="callout-title">
                                <span className="callout-icon-holder mr-1">
                                    <i className={`fas fa-${callout.icon}`}></i>
                                </span>
                                {callout.title}
                            </h4>
                            {
                                typeof component.props.children === "string"
                                    ? component.props.children.replace(typeRegex, "")
                                    : component.props.children.map(child => typeof child === "string" ? child.replace(typeRegex, "") : child)
                            }
                        </div>
                    </div>
            )}
        },
        {
            shouldProcessNode: node => node.name && node.name === "a" && node.attribs.href.startsWith("#"),
            processNode: (node, children) => <a href={`${node.attribs.href}`} className="scrollto">{children}</a>
        },
        {
            shouldProcessNode: node => node.parent && node.parent.name && node.parent.name === "code" && (clientId !== "" || accessToken !== ""),
            processNode: (node, children) => {
                if(clientId !== "") {
                    node.data = node.data.replace("{client_id}", clientId);
                }

                if(accessToken !== "") {
                    node.data = node.data.replace("{access_token}", accessToken);
                }

                return node.data;
            }
        },
        {
            shouldProcessNode: node => node.name && node.name === "pre" && node.children[0].attribs.class !== "language-text",
            processNode: (node, children) => {
                return (
                    <pre style={{ position: "relative" }}>
                        { children[0].props.children.match(/\n/g).length > 1 &&
                            <button data-toggle="tooltip" data-placement="left" title="Tooltip on left" key={"random"} className='btn btn-sm btn-light btn-clipboard' style={{ position: "absolute", right: "2px", top: "2px" }}>
                                <i className='fa fa-fw fa-copy'></i>
                            </button>
                        }

                        {children}
                    </pre>
                )
            }
        },
        {
            shouldProcessNode: node => node.name && node.name === "p" && node.children.find(child => child.name && child.name === "img"),
            processNode: (node, children) => {
                const props = children[0].props;
                return <LightboxImage src={`${process.env.PUBLIC_URL}/${props.src}`} alt={props.alt} group={Md5(props.src).substr(2,9)} key={`${Md5(props.src + props.alt)}`} />
            }
        },
        {
            shouldProcessNode: node => node.name && node.name === "table",
            processNode: (node, children) => (
                <div className="table-responsive" key={Math.random()}>
                    <table className="table table-striped table-hover">
                        { children }
                    </table>
                </div>
            )
        },
        {
            shouldProcessNode: node => true,
            processNode: processNodeDefinitions.processDefaultNode
        }
    ];

    return (
        <>
            {new HtmlToReactParser().parseWithInstructions(content, () => true, processingInstructions)}
        </>
    );
}