import React from 'react';

export default function Lightbox({src, alt, group}) {
    return (
        <figure className="figure docs-figure py-3">
            <a href={src} data-title={alt} data-lightbox={group}>
                <img className="figure-img img-fluid shadow rounded" src={src} alt={alt} />
            </a>

            <figcaption className="figure-caption mt-3">
                <i className="fas fa-info-circle mr-2"></i> {alt}
            </figcaption>
        </figure>
    );
}