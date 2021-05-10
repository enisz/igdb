import React from 'react';

export default function MainLink({paragraph}) {
    return (
        <li className="nav-item section-title" key={`sidebar-main-link-${paragraph.id}`} style={{ marginTop: "16px"}}>
            <a className="nav-link scrollto" href={`#${paragraph.slug}`}>
                <span className="theme-icon-holder mr-2">
                    <i className={`fas ${paragraph.icon}`}></i>
                </span>
                {paragraph.title}
            </a>
        </li>
    );
}