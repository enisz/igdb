import md5 from 'md5';
import React, { useState, Fragment } from 'react';
import { getParagraphs } from '../utils/Database';
import HtmlParser from './HtmlParser';

export default function DocumentSections({parentId}) {
    const [paragraphs] = useState(getParagraphs({ parent : parentId}));

    return (
        <>
            { paragraphs.length > 0 && paragraphs.map( paragraph => (
                <Fragment key={paragraph.id}>
                    <section className="docs-section" id={paragraph.slug}>
                        { React.createElement(`h${paragraph.level}`, {}, paragraph.title ) }
                        {/*<h2 className="section-heading">{paragraph.title}</h2>*/}
                        <HtmlParser content={paragraph.body.html} />
                    </section>

                    { getParagraphs({ parent : paragraph.id }).length > 0 && <DocumentSections parentId={paragraph.id} key={"section-" + md5(paragraph.id)} /> }
                </Fragment>
            )) }
        </>
    );
}