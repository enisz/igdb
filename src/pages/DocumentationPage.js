import React, { useEffect, useState } from 'react';
import Footer from '../components/Footer';
import Header from '../components/Header';
import { getParagraphs } from '../utils/Database';
import useBodyClass from '../hooks/useBodyClass';
import Sidebar from '../components/Sidebar';
import HighlightJS from 'highlight.js';
import DocumentSections from '../components/DocumentSections';
import HtmlParser from '../components/HtmlParser';

export default function DocumentationPage() {
    useBodyClass("docs-page");
    const [topics] = useState(getParagraphs({ parents : {"$size" : 0} }));

    useEffect(() => {
        HighlightJS.highlightAll();

        /* wmooth scrolling on page load if URL has a hash */
		setTimeout(
            () => {
                if(window.location.hash) {
                    var urlhash = window.location.hash;
                    window.jQuery('body').scrollTo(urlhash, 800, {offset: -69, 'axis':'y'});
                }
            }, 100
        )
    }, []);

    const calculateElapsedTime = timestamp => {
        const time = +new Date();
        const difference = time - timestamp;
        const units = [1000, 60, 60, 24, 7, 4, 12];
        const textual = ["millisecond", "second", "minute", "hour", "day", "week", "year"]
        let counter = difference;

        let i;
        for(i=0; i<units.length; i++) {
            if(Math.floor(counter / units[i]) > 0) {
                counter = Math.floor(counter / units[i]);
            } else {
                break;
            }
        }

        return counter + " " + textual[i] + (counter > 1 ? "s" : "");
    }


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
                                    <h1 className="docs-heading">{topic.title} <span className="docs-time"><i className="far fa-clock mr-1"></i>Last updated: {topic.date} { topic.timestamp != null && <>({ calculateElapsedTime(topic.timestamp)} ago)</>}</span></h1>

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

//const sections = () => {
//    return (
    //        {{ ByParent(topic.id).length > 0 && ByParent(topic.id).map( paragraph => (
    //            <section className="docs-section" id={paragraph.id} key={paragraph.id}>
    //                <h2 className="section-heading">{paragraph.title}</h2>

    //                {new HtmlToReactParser().parseWithInstructions(paragraph.body.html, () => true, processingInstructions)}
    //            </section>
    //        )) }}
//    );
//}