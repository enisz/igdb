import React from 'react';

export default function SubLink({paragraph}) {
    return (
        <li className="nav-item">
            <a className="nav-link scrollto" href={`#${paragraph.slug}`} style={{ paddingLeft : (paragraph.level - 1) + "5px" }} >
                {paragraph.title}
            </a>
        </li>
    );
}