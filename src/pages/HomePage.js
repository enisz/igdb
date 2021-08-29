import React, { useEffect, useRef, useState } from 'react';
import Header from '../components/Header';
import Footer from '../components/Footer';
import { NavLink } from 'react-router-dom';
import { getParagraphs } from '../utils/Database';
import { Modal } from 'bootstrap';
import useSearch from '../hooks/useSearch';
import GitHubButton from 'react-github-btn';
import './HomePage.scss';
import useUserToken from '../hooks/useUserTokens';
import { HashLink } from 'react-router-hash-link';

export default function HomePage() {
    const [topics] = useState(getParagraphs({ parents : {"$size" : 0} }));
    const {searchTerm, setSearchTerm, handleSearch} = useSearch("");
    let infoModal = useRef(null);
    const {clientId, setClientId, accessToken, setAccessToken, storeTokens, setStoreTokens} = useUserToken();

    useEffect(() => {
        infoModal.current = new Modal(document.getElementById('exampleModal'), {
            backdrop : true,
            keyboard : true,
            focus : true
        })
    }, []);

    return (
        <>
            <Header hamburger={false} />

            <div className="page-header theme-bg-dark py-5 text-center position-relative">
                <div className="theme-bg-shapes-right"></div>
                <div className="theme-bg-shapes-left"></div>
                <div className="container">
                    <h1 className="page-heading single-col-max mx-auto">Documentation</h1>
                    <div className="page-intro single-col-max mx-auto">IGDB PHP API Wrapper</div>
                    <div className="main-search-box pt-3 d-block mx-auto">
                        <form className="search-form w-100" onSubmit={handleSearch}>
                            <input type="text" placeholder="Search the docs..." name="search" className="form-control search-input" value={searchTerm} onChange={event => setSearchTerm(event.target.value)} />
                            <button type="submit" className="btn search-btn" value="Search"><i className="fas fa-search"></i></button>
                        </form>
                    </div>

                    <div className="github-container d-sm-none">
                        <GitHubButton href="https://github.com/enisz" data-size="small" data-show-count="true" aria-label="Follow @enisz on GitHub">Follow @enisz</GitHubButton>
                        <GitHubButton href="https://github.com/enisz/igdb" data-icon="octicon-star" data-size="small" data-show-count="true" aria-label="Star enisz/igdb on GitHub">Star</GitHubButton>
                        <GitHubButton href="https://github.com/enisz/igdb/subscription" data-icon="octicon-eye" data-size="small" data-show-count="true" aria-label="Watch enisz/igdb on GitHub">Watch</GitHubButton>
                        <GitHubButton href="https://github.com/enisz/igdb/fork" data-icon="octicon-repo-forked" data-size="small" data-show-count="true" aria-label="Fork enisz/igdb on GitHub">Fork</GitHubButton>
                    </div>

                    <div className="github-container d-none d-sm-block">
                    <GitHubButton href="https://github.com/enisz" data-size="large" data-show-count="true" aria-label="Follow @enisz on GitHub">Follow @enisz</GitHubButton>
                        <GitHubButton href="https://github.com/enisz/igdb" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star enisz/igdb on GitHub">Star</GitHubButton>
                        <GitHubButton href="https://github.com/enisz/igdb/subscription" data-icon="octicon-eye" data-size="large" data-show-count="true" aria-label="Watch enisz/igdb on GitHub">Watch</GitHubButton>
                        <GitHubButton href="https://github.com/enisz/igdb/fork" data-icon="octicon-repo-forked" data-size="large" data-show-count="true" aria-label="Fork enisz/igdb on GitHub">Fork</GitHubButton>
                    </div>
                </div>
            </div>

            <div className="page-content">
                <div className="container">
                    <div className="row mt-5">
                        <div className="col-12 text-center">
                            <HashLink to="/home" onClick={() => infoModal.current.toggle()}>Add your tokens to the example codes</HashLink>
                        </div>
                    </div>
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

            <div className="modal fade" ref={infoModal} id="exampleModal" tabIndex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div className="modal-dialog modal-lg">
                    <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="exampleModalLabel">IGDB Tokens in the documentation</h5>
                    </div>
                    <div className="modal-body">
                        <div className="container-fluid">
                            <div className="row">
                                <div className="col-12">
                                    <p>The documenation has a lot of example codes where the IGDB wrapper class has to be instantiated with your personal tokens. If you provide your tokens in the form below, the example codes will have your own tokens and you will have copy-paste codes in the documentation.</p>
                                    <p>These tokens are not stored anywhere else but in your browser. If you check the "Remember my tokens" checkbox, your tokens will be stored in your local storage and will be filled automatically on your next visit.</p>
                                </div>

                                <div className="col-12 col-lg-6 mb-3">
                                    <label htmlFor="client-id">Client ID</label>
                                    <input type="text" placeholder="Client ID" id="client-id" className="form-control form-control-sm" value={clientId} onChange={event => setClientId(event.target.value)} />
                                </div>

                                <div className="col-12 col-lg-6 mb-3">
                                <label htmlFor="client-id">Access Token</label>
                                    <input type="text" placeholder="Access Token" id="access-token" className="form-control form-control-sm" value={accessToken} onChange={event => setAccessToken(event.target.value)} />
                                </div>

                                <div className="col-12 col-lg-6 mb-3">
                                    <div className="form-check">
                                        <input className="form-check-input" type="checkbox"  id="flexCheckDefault" checked={storeTokens} onChange={event => setStoreTokens(event.target.checked)} />
                                        <label className="form-check-label" htmlFor="flexCheckDefault">
                                            Remember my tokens
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="modal-footer">
                        <button className="btn btn-danger btn-sm" onClick={() => { setAccessToken(""); setClientId(""); }}>Delete the tokens</button>
                        <button type="button" className="btn btn-primary btn-sm" data-bs-dismiss="modal" onClick={() => infoModal.current.toggle()}>Close</button>
                    </div>
                    </div>
                </div>
            </div>

            <Footer />
        </>
    )
}