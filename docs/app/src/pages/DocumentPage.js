import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router';
import { NavLink } from 'react-router-dom';
import useBodyClass from '../hooks/useBodyClass';
import { getTopic, getParagraphs } from '../utils/Database';
import HighlightJS from 'highlight.js';
import HtmlToReact from 'html-to-react';
import useUserTokens from '../hooks/useUserTokens';
import Md5 from 'md5';

export default function DocumentPage() {
    const params = useParams();
    const [paragraphs, setParagraphs] = useState([]);
    const [topic, setTopic] = useState({});
    const [theme, setTheme] = useState("");
    const {clientId, accessToken} = useUserTokens();

    useBodyClass("body-" + theme);

    useEffect(() => {
        const topicInfo = getTopic(params.document);

        setTopic(topicInfo);
        setParagraphs(generateStructure(getParagraphs(topicInfo.id)));
        setTheme(topicInfo.color);

        setTimeout(() => {
            // Activate scrollspy menu
            window.jQuery('body').scrollspy({target: '#doc-menu', offset: 100});

            // Smooth scrolling
            window.jQuery('a.scrollto').on('click', function(e){
                const target = this.hash;
                e.preventDefault();
                window.jQuery('body').scrollTo(target, 800, {offset: 0, 'axis':'y'});
            });

            HighlightJS.highlightAll();
        }, 100)
    }, [params.document]);


    // Generating the render structure
    const generateStructure = paragraphs => {
        let structure = [];
        let submenu = [];

        for(let i=0; i<paragraphs.length; i++) {
            const current = paragraphs[i];
            const next = i+1 <= paragraphs.length ? paragraphs[i+1] : null;

            if(current.level === 1) {
                structure.push(current);
            } else {
                submenu.push(current);

                if(next == null || next.level === 1) {
                    structure.push(submenu);
                    submenu = [];
                }
            }
        }

        return structure;
    }

    // Generating sections
    const sections = paragraphs => {
        let output = [];
        const HtmlToReactParser = HtmlToReact.Parser;
        const processNodeDefinitions = new HtmlToReact.ProcessNodeDefinitions(React);
        const processingInstructions = [
            {
                shouldProcessNode: node => node.name && node.name === "blockquote",
                processNode: (node, children) => (
                    <div key={`callout-${Md5(children[0]).substr(0,10)}`} className="callout-block callout-info">
                        <div className="icon-holder">
                            <i className="fas fa-info-circle"></i>
                        </div>
                        <div className="content">
                            <h4 className="callout-title">Note</h4>
                            {children}
                        </div>
                    </div>
                )
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
                shouldProcessNode: node => true,
                processNode: processNodeDefinitions.processDefaultNode
            }
        ];

        for(let i=0; i<paragraphs.length; i++) {
            const current = paragraphs[i];
            const next = i+1 <= paragraphs.length ? paragraphs[i+1] : null;

            if(current.length === undefined) {
                output.push((
                    <section key={`${current.id}-section`} id={current.id} className="doc-section">
                        <h2 className="section-title">{current.title}</h2>
                        <div className="section-block">
                            { new HtmlToReactParser().parseWithInstructions(current.body.html, () => true, processingInstructions) }
                        </div>

                        { next != null && next.length !== undefined && next.map( subitem => (
                            <div key={subitem.id} id={subitem.id} className="section-block">
                                <h3 className="block-title">{subitem.title}</h3>
                                    { new HtmlToReactParser().parseWithInstructions(subitem.body.html, () => true, processingInstructions) }
                            </div>
                        )) }
                    </section>
                ));
            }
        }

        return output;
    }

    // building the menu
    const buildMenu = structure => {
        const link = paragraph => <a key={paragraph.id} className="nav-link scrollto" href={`#${paragraph.id}`}>{paragraph.title}</a>
        let menu = [];

        for(let i=0; i<structure.length; i++) {
            const current = structure[i];

            // object
            if(current.length === undefined) {
                menu.push(link(current))
            } else { // array
                menu.push(
                    <nav key={`submenu${i}`} className="doc-sub-menu nav flex-column">
                        {current.map( paragraph => link(paragraph) )}
                    </nav>
                )
            }
        }

        return menu;
    }

    return (
        <>
            <header id="header" className="header">
                <div className="container">
                    <div className="branding">
                        <h1 className="logo">
                            <NavLink to="/home">
                                <span aria-hidden="true" className="icon_documents_alt icon"></span>
                                <span className="text-highlight">IGDB</span><span className="text-bold">Wrapper</span>
                            </NavLink>
                        </h1>

                    </div>

                    <ol className="breadcrumb">
                        <li className="breadcrumb-item"><NavLink to="/home">Home</NavLink></li>
                        <li className="breadcrumb-item active">{topic.title}</li>
                    </ol>

                    <div className="top-search-box">
                        <form className="form-inline search-form justify-content-center" action="" method="get">

                            <input type="text" placeholder="Search..." name="search" className="form-control search-input" />

                            <button type="submit" className="btn search-btn" value="Search"><i className="fas fa-search"></i></button>

                        </form>
                    </div>

                </div>
            </header>
            <div className="doc-wrapper">
                <div className="container">
                    <div id="doc-header" className="doc-header text-center">
                        <h1 className="doc-title">{Object.keys(topic).length > 0 && <i className={`icon fa ${topic.icon}`}></i>} {topic.title}</h1>
                        <div className="meta"><i className="far fa-clock"></i> Last updated: Oct 12th, 2020</div>
                    </div>
                    <div className="doc-body row">
                        <div className="doc-content col-md-9 col-12 order-1">
                            <div className="content-inner">
                                { paragraphs.length > 0 && sections(paragraphs) }
                            </div>
                        </div>
                        <div className="doc-sidebar col-md-3 col-12 order-0 d-none d-md-flex">
                            <div id="doc-nav" className="doc-nav">
                                <nav id="doc-menu" className="nav doc-menu flex-column sticky">
                                    {paragraphs.length > 0 && buildMenu(paragraphs)}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}