import React from 'react';

export default function SubLink({paragraph}) {
    return (
        <li className="nav-item">
            <a className="nav-link scrollto" href={`#${paragraph.slug}`} >
                {paragraph.title}
            </a>
        </li>
    );
}