import React, { useEffect, useRef, useState } from 'react';
import Header from '../components/Header';
import Footer from '../components/Footer';
import { NavLink } from 'react-router-dom';
import { getParagraphs } from '../utils/Database';
import { Modal } from 'bootstrap';

export default function HomePage() {
    const [topics] = useState(getParagraphs({ parents : {"$size" : 0} }));
    let infoModal = useRef(null);

    useEffect(() => {
        infoModal.current = new Modal(document.getElementById('exampleModal'), {
            backdrop : true,
            keyboard : true,
            focus : true
        })
    }, []);

    return (
        <>
            <Header />

            <div className="page-header theme-bg-dark py-5 text-center position-relative">
                <div className="theme-bg-shapes-right"></div>
                <div className="theme-bg-shapes-left"></div>
                <div className="container">
                    <h1 className="page-heading single-col-max mx-auto">Documentation</h1>
                    <div className="page-intro single-col-max mx-auto">IGDB PHP API Wrapper</div>
                    <div className="main-search-box pt-3 d-block mx-auto">
                        <form className="search-form w-100">
                            <input type="text" placeholder="Search the docs..." name="search" className="form-control search-input" />
                            <button type="submit" className="btn search-btn" value="Search"><i className="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>

            <div className="page-content">
                <div className="container">
                    <div className="docs-overview py-5">
                        <div className="row justify-content-center">
                            { topics.length > 0 && topics.map( topic =>
                                <div key={topic.id} className="col-12 col-lg-4 py-3">
                                    <div className="card shadow-sm">
                                        <div className="card-body">
                                            <h5 className="card-title mb-3">
                                                <span className="theme-icon-holder card-icon-holder mr-2">
                                                    <i className={`fas ${topic.icon}`}></i>
                                                </span>
                                                <span className="card-title-text">{topic.title}</span>
                                            </h5>
                                            <div className="card-text">
                                                {topic.overview}
                                            </div>
                                            <NavLink to={`/documentation#${topic.slug}`} className="card-link-mask"></NavLink>
                                        </div>
                                    </div>
                                </div>
                            ) }
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" className="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" onClick={() => infoModal.current.toggle()}>
            Launch demo modal
            </button>


            <div className="modal fade" ref={infoModal} id="exampleModal" tabIndex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div className="modal-dialog">
                    <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="exampleModalLabel">Modal title</h5>
                    </div>
                    <div className="modal-body">
                        ...
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-secondary" data-bs-dismiss="modal" onClick={() => infoModal.current.toggle()}>Close</button>
                    </div>
                    </div>
                </div>
            </div>

            <Footer />
        </>
    )
}