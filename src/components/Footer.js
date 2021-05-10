import React from 'react';

export default function Footer() {
    return (
        <footer className="footer">
            <div className="footer-bottom text-center py-5">
                <ul className="social-list list-unstyled pb-4 mb-0">
                    <li className="list-inline-item"><a href="https://github.com/enisz/igdb"><i className="fab fa-github fa-fw"></i></a></li>
                </ul>

                <small className="copyright">
                    Designed with <i className="fas fa-heart" style={{ color : "#fb866a"}}></i> by <a className="theme-link" href="http://themes.3rdwavemedia.com" target="_blank" rel="noreferrer">Xiaoying Riley</a> for developers
                </small>
            </div>

        </footer>
    );
}