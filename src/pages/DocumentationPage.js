import React, { useEffect, useState, useMemo } from 'react';
import dateParser from 'node-date-parser';
import Footer from '../components/Footer';
import Header from '../components/Header';
import { getParagraphs } from '../utils/Database';
import useBodyClass from '../hooks/useBodyClass';
import Sidebar from '../components/Sidebar';
import HighlightJS from 'highlight.js';
import DocumentSections from '../components/DocumentSections';
import HtmlParser from '../components/HtmlParser';
import TimeAgo from 'javascript-time-ago'
import en from 'javascript-time-ago/locale/en'
import useToastContext from '../hooks/useToastContext';


TimeAgo.addDefaultLocale(en);
const timeAgo = new TimeAgo('en-US');

export default function DocumentationPage() {
    useBodyClass("docs-page");
    const [topics] = useState(getParagraphs({ parents : {"$size" : 0} }));
    const toast = useToastContext();

    const clipboardClick = useMemo(() => event => {
        const content = event.currentTarget.nextElementSibling.textContent;

        if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(content);
            toast.success("Code snippet copied to the clipboard!")
        } else {
            toast.error("Failed to copy the code snippet to the clipboard!");
        }
    }, [toast])

    useEffect(() => {
        HighlightJS.highlightAll();

		setTimeout(
            () => {
                if(window.location.hash) {
                    var urlhash = window.location.hash;
                    window.jQuery('body').scrollTo(urlhash, 800, {offset: -69, 'axis':'y'});
                    window.jQuery(".btn-clipboard").on("click", clipboardClick)
                }
            }, 200
        )

        return () => window.jQuery(".btn-clipboard").off("click", clipboardClick);
    }, [toast, clipboardClick]);

    return (
        <>
            <Header searchBar />

            <div className="docs-wrapper">
                <Sidebar />

                <div className="docs-content">
                    <div className="container">
                        { topics.length > 0 && topics.map( topic => (
                            <article className="docs-article" id={topic.slug} key={topic.id}>
                                <header className="docs-header">
                                    <h1 className="docs-heading">
                                        {topic.title}&nbsp;
                                        <span className="docs-time">
                                            <i className="far fa-clock mr-1"></i>
                                            Last updated:&nbsp;
                                            { topic.timestamp
                                                ?
                                                    <>
                                                        {dateParser.parse('j', new Date(topic.timestamp))}
                                                        <sup>{dateParser.parse('o', new Date(topic.timestamp))}</sup>&nbsp;
                                                        of&nbsp;
                                                        {dateParser.parse('F, Y', new Date(topic.timestamp))}&nbsp;
                                                        ({timeAgo.format(new Date(topic.timestamp))})
                                                    </>
                                                :
                                                <>Not published yet</>
                                            }
                                        </span>
                                    </h1>
                                    <section className="docs-intro">
                                        <HtmlParser content={topic.body.html} />
                                    </section>
                                </header>

                                <DocumentSections parentId={topic.id} />
                            </article>
                        ))}
                    </div>
                </div>
            </div>

            <Footer />
        </>
    );
}