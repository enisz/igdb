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
                return (
                    <div className="callout-block callout-block-info" key={`callout-${id}`}>
                        <div className="content">
                            <h4 className="callout-title">
                                <span className="callout-icon-holder mr-1">
                                    <i className="fas fa-info-circle"></i>
                                </span>
                                Note
                            </h4>
                            {children}
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