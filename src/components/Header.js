import React, { useEffect } from 'react';
import { NavLink } from 'react-router-dom';
import useSearch from '../hooks/useSearch';

export default function Header({searchBar = false, searchValue = "", hamburger = true}) {
    const {searchTerm, setSearchTerm, handleSearch} = useSearch(searchValue);

    useEffect(() => {
        /* ====== Toggle Sidebar ======= */
		window.jQuery('#docs-sidebar-toggler').on('click', function(){
			if ( window.jQuery('#docs-sidebar').hasClass('sidebar-visible') ) {
				window.jQuery("#docs-sidebar").removeClass('sidebar-visible').addClass('sidebar-hidden');
			} else {
				window.jQuery("#docs-sidebar").removeClass('sidebar-hidden').addClass('sidebar-visible');
			}
		});
    }, []);

    return (
        <header className="header fixed-top">
            <div className="branding docs-branding">
                <div className="container-fluid position-relative py-2">
                    <div className="docs-logo-wrapper">
                        { hamburger && <button id="docs-sidebar-toggler" className="docs-sidebar-toggler docs-sidebar-visible mr-2 d-xl-none" type="button">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>}
                        <div className="site-logo">
                            <NavLink to="/home" className="navbar-brand">
                            <img className="logo-icon mr-2" src={`${process.env.PUBLIC_URL}/coderdocs-logo.svg`} alt="logo" />
                                <span className="logo-text">
                                    IGDB
                                    <span className="text-alt">
                                        Wrapper
                                    </span>
                                </span>
                            </NavLink>
                        </div>
                    </div>
                    <div className="docs-top-utilities d-flex justify-content-end align-items-center">
                        { searchBar && (
                            <div className="top-search-box d-none d-lg-flex">
                                <form className="search-form" onSubmit={handleSearch}>
                                    <input type="text" placeholder="Search the docs..." name="search" className="form-control search-input" value={searchTerm} onChange={event => setSearchTerm(event.target.value)} />
                                    <button type="submit" className="btn search-btn" value="Search"><i className="fas fa-search"></i></button>
                                </form>
                            </div>
                        )}

                        {/*<ul className="social-list list-inline mx-md-3 mx-lg-5 mb-0 d-none d-lg-flex">
                            <li className="list-inline-item"><a href="https://github.com/enisz/igdb"><i className="fab fa-github fa-fw"></i></a></li>
                        </ul>*/}
                        <a href="https://github.com/enisz/igdb/archive/master.zip" className="btn btn-primary d-none d-lg-flex ml-3">Download</a>
                    </div>
                </div>
            </div>
        </header>
    );
}