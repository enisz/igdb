import React, { useEffect, useState } from 'react';
import { getParagraphs } from '../utils/Database';
import SidebarMainLink from './SidebarMainLink';
import SidebarSubLink from './SidebarSubLink';

export default function Sidebar() {
	const [paragraphs] = useState(getParagraphs({}));

	useEffect(() => {
		window.jQuery(window).on('resize', function() {
			//Add/remove class based on browser size when load/resize
			var w = window.jQuery(window).width();

			if(w >= 1200) {
				// if larger
				window.jQuery('#docs-sidebar').addClass('sidebar-visible').removeClass('sidebar-hidden');
			} else {
				// if smaller
				window.jQuery('#docs-sidebar').addClass('sidebar-hidden').removeClass('sidebar-visible');
			}
		}).resize();

		/* ===== Smooth scrolling ====== */
		window.jQuery('#docs-sidebar a.scrollto').on('click', function(e){
			const target = this.hash;
			//e.preventDefault();
			window.jQuery('body').scrollTo(target, 800, {offset: -69, 'axis':'y'});

			//Collapse sidebar after clicking
			if (window.jQuery('#docs-sidebar').hasClass('sidebar-visible') && window.jQuery(window).width() < 1200){
				window.jQuery('#docs-sidebar-toggler').click();
			}
		});

		/* ====== Activate scrollspy menu ===== */
		window.jQuery('body').scrollspy({target: '#docs-nav', offset: 100});
	}, []);

    return (
        <div id="docs-sidebar" className="docs-sidebar">
		    <div className="top-search-box d-lg-none p-3">
                <form className="search-form">
		            <input type="text" placeholder="Search the docs..." name="search" className="form-control search-input" />
		            <button type="submit" className="btn search-btn" value="Search"><i className="fas fa-search"></i></button>
		        </form>
            </div>
		    <nav id="docs-nav" className="docs-nav navbar">
			    <ul className="section-items list-unstyled nav flex-column pb-3">
					{paragraphs.length > 0 && paragraphs.map(paragraph => paragraph.parent == null ? <SidebarMainLink paragraph={paragraph} key={`sidebar-main-link-${paragraph.id}`} /> : <SidebarSubLink paragraph={paragraph} key={`sidebar-sub-link-${paragraph.id}`} />)}
			    </ul>
		    </nav>
	    </div>
    );
}