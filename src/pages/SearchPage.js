import React, { Fragment, useEffect, useState } from 'react';
import { useLocation } from 'react-router';
import { NavLink } from 'react-router-dom';
import Footer from '../components/Footer';
import Header from '../components/Header';
import HtmlParser from '../components/HtmlParser';
import { getParagraphs } from '../utils/Database';
import HighlightJS from 'highlight.js';

export default function SearchPage() {
    const location = useLocation();
    const queryString = decodeURIComponent(new URLSearchParams(location.search).get("q"));
    const [matches, setMatches] = useState([]);

    useEffect(() => {
        setMatches(getParagraphs({
            "$or" : [
                { "body.stripped" : { "$contains" : queryString } },
                { title : { "$contains" : queryString } }
            ]
        }));

        setTimeout(() => HighlightJS.highlightAll(), 500)
    }, [queryString]);

    const getTopicIcon = paragraph => paragraph.level === 1 ? paragraph.icon : getParagraphs({ id : paragraph.parents[0]})[0].icon;
    const getParagraphPath = paragraph => {
        let titles = [];

        if(paragraph.parents.length > 0) {
            for(let index in paragraph.parents) {
                const id = paragraph.parents[index];
                titles.push(getParagraphs({ id : id })[0].title)
            }
        }

        titles.push(paragraph.title);

        return titles;
    }

    const resultCard = paragraph => (
        <div key={paragraph.id} className="col-12 py-3">
            <div className="card shadow-sm">
                <div className="card-body">
                    <h5 className="card-title mb-3">
                        <span className="theme-icon-holder card-icon-holder mr-2">
                            <i className={`fas ${getTopicIcon(paragraph)}`}></i>
                        </span>

                        <span className='card-title-text'>{getParagraphPath(paragraph).map((title, index) => <Fragment key={`path-${index}`}>{index > 0 && <i style={{marginLeft: "5px"}} className="fas fa-angle-right fa-fw"></i>} {title}</Fragment>)}</span>
                    </h5>
                    <div className="card-text">
                        <HtmlParser content={paragraph.body.html} />
                    </div>
                    <NavLink to={`/documentation#${paragraph.slug}`} className="card-link-mask"></NavLink>
                </div>
            </div>
        </div>
    )

    return (
        <>
            <Header searchBar searchValue={queryString} />

            <div className="page-header theme-bg-dark py-5 text-center position-relative">
                <div className="theme-bg-shapes-right"></div>
                <div className="theme-bg-shapes-left"></div>
                <div className="container">
                    <h1 className="page-heading single-col-max mx-auto">Search Results</h1>
                    <div className="page-intro single-col-max mx-auto">{matches.length} matches for "{queryString}"</div>
                </div>
            </div>

            <div className="page-content">
                <div className="container">
                    <div className="docs-overview py-5">
                        <div className="row">
                            <div className="col-12">
                                { matches.length
                                    ? <><h6>{matches.length} matches for "{queryString}"</h6><hr/></>
                                    : <><h6>No matches for "{queryString}"</h6></>
                                }
                            </div>
                        </div>
                        <div className="row justify-content-center">
                            { matches.length > 0 && matches.map( match => resultCard(match) ) }
                        </div>
                    </div>
                </div>
            </div>

            <Footer />
        </>
    );
}