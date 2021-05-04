import React, { useEffect, useState } from 'react';
import { NavLink } from 'react-router-dom';
import useBodyClass from '../hooks/useBodyClass';
import { getTopics } from '../utils/Database';
import GitHubButton from 'react-github-btn'
import useUserTokens from '../hooks/useUserTokens';
import './HomePage.scss';

export default function HomePage() {
    useBodyClass("landing-page");
    const [topics, setTopics] = useState([]);
    const {clientId, setClientId, accessToken, setAccessToken, storeTokens, setStoreTokens} = useUserTokens();

    useEffect(() => {
        setTopics(getTopics());
    }, []);

    return (
        <>
            <header className="header text-center">
                <div className="container">
                    <div className="branding">
                        <h1 className="logo">
                            <span aria-hidden="true" className="icon_documents_alt icon"></span>
                            <span className="text-highlight">IGDB</span><span className="text-bold">Wrapper</span>
                        </h1>
                    </div>
                    <div className="tagline">
                        <p>IGDB API PHP Wrapper Documentation</p>
                    </div>

                    <div className="main-search-box pt-3 pb-4 d-inline-block">
                        <form className="form-inline search-form justify-content-center" action="" method="get">

                            <input type="text" placeholder="Enter search terms..." name="search" className="form-control search-input" />

                            <button type="submit" className="btn search-btn" value="Search"><i className="fas fa-search"></i></button>

                        </form>
                    </div>

                    <div className="social-container">

                        <div className="github-btn mb-2">
                            <GitHubButton href="https://github.com/enisz/igdb" data-size="large" data-show-count="true" aria-label="Star enisz/igdb on GitHub">Star</GitHubButton>
                            <GitHubButton href="https://github.com/enisz/igdb/fork" data-size="large" data-show-count="true" aria-label="Fork enisz/igdb on GitHub">Fork</GitHubButton>
                            <GitHubButton href="https://github.com/enisz" data-size="large" data-show-count="true" aria-label="Follow @enisz on GitHub">Follow @enisz</GitHubButton>
                        </div>

                        <div className="fb-like" data-href="https://themes.3rdwavemedia.com/" data-width="" data-layout="button_count" data-action="like" data-size="small" data-share="true"></div>
                    </div>
                </div>
            </header>

            <section className="cards-section text-center">
                <div className="container">
                    <h2 className="title">Introduction</h2>
                    <p>This is the documentation of the <a href="http://github.com/enisz/igdb" target="_blank" title="IGDB PHP API Wrapper" rel="noreferrer">API Wrapper Class</a> of the <a href="https://www.igdb.com/" target="_blank" title="IGDB" rel="noreferrer">IGDB Database</a> written in PHP. The wrapper's main purpose is to provide a simple solution to fetch data from the remote database. The latest files can be downloaded from the GitHub repository by clicking the Download button below.</p>
                    <div className="intro">

                        <div className="cta-container">
                            <a className="btn btn-primary btn-cta" href="https://github.com/enisz/igdb/archive/master.zip"><i className="fas fa-cloud-download-alt"></i> Download</a>
                        </div>
                    </div>

                    <h2 className="title">IGDB Tokens</h2>
                    <p>If you have your tokens already you can fill this form below. If this form is filled, the example codes in this documentation will be filled with your tokens providing you easy copy-paste examples. If you check the "Store my tokens" checkbox, your tokens will be stored in your browser's local storage and will be automatically filled the next time you visit. If not checked, the tokens will be deleted when the browser is closed. </p>
                    <div className="intro">
                        <div className="cta-container">
                            <form autocomplete="off" onSubmit={event => event.preventDefault()}>
                                <div className="row">
                                    <div className="col-12 col-md-6 mb-3">
                                        <label className="form-check-label mb-1" htmlFor="client-id-token" style={{ fontWeight : "bold" }}>Client ID</label>
                                        <input type="text" className="form-control" id="client-id-token" placeholder="Client ID" style={{fontFamily: "Courier New"}} value={clientId} onChange={event => setClientId(event.target.value) } />
                                    </div>
                                    <div className="col-12 col-md-6 mb-3">
                                        <label className="form-check-label mb-1" htmlFor="access-token-token" style={{ fontWeight : "bold" }}>Access Token</label>
                                        <input type="text" className="form-control" id="access-token" placeholder="Access Token" style={{fontFamily: "Courier New"}} value={accessToken} onChange={event => setAccessToken(event.target.value) } />
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-12 col-sm-6 mb-3">
                                        <div className="form-check">
                                            <input className="form-check-input" type="checkbox" value="" id="store-tokens" checked={storeTokens} onChange={event => setStoreTokens(event.target.checked) } />
                                            <label className="form-check-label" htmlFor="store-tokens">
                                                Store my tokens
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-12 col-sm-6 mb-3">
                                        <button className="btn btn-danger btn-cta" onClick={() => { setClientId(""); setAccessToken("") }}><i className="fas fa-trash-alt"></i> Delete My Tokens</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="cards-wrapper" className="cards-wrapper row">
                        { topics.length === 0 ? <div>Loading...</div> : topics.map( topic => card(topic) ) }
                    </div>
                </div>
            </section>
        </>
    )
}

const card = ({ id, title, icon, color, overview }) => (
    <div key={id} className={`item item-${color} col-lg-4 col-6`}>
        <div className="item-inner">
            <div className="icon-holder">
                <i className={`icon fa ${icon}`}></i>
            </div>
            <h3 className="title">{title}</h3>
            <p className="intro">{overview}</p>
            <NavLink className="link" to={`/document/${id}`}>
                <span>
                </span>
            </NavLink>
        </div>
    </div>
)