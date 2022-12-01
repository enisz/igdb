import React from 'react';

export default function Lightbox({src, alt, group}) {
    return (
        <figure className="figure docs-figure py-3 w-100 text-center">
            <a href={src} data-title={alt} data-lightbox={group}>
                <img className="figure-img img-fluid shadow rounded" src={src} alt={alt} />
            </a>

            <figcaption className="figure-caption mt-3">
                {alt}
            </figcaption>
        </figure>
    );
}