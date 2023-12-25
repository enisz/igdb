import RemoveMarkdown from "remove-markdown";
import Slug from 'slug';
import { Marked, MarkedExtension, RendererObject } from 'marked';
import { templatePath } from "..";
import Path from 'path';

export default class StringR {
    private static marked: Marked;

    public static capitalize(string: string): string {
        return string.charAt(0).toUpperCase() + string.substring(1);
    }

    private static getMarkedInstance(): Marked {
        if(this.marked) {
            return this.marked;
        }

        const markedExtension: MarkedExtension = {
            async: false,
            breaks: true,
            gfm: true,
            renderer: {
                image: this.image,
                blockquote: this.blockquote,
                link: this.link
            }
        };

        this.marked = new Marked(markedExtension);
        return this.marked;
    }

    public static toHtml(markdown: string): string {
        return this.getMarkedInstance().parse(markdown) as string;
    }

    public static toStripped(markdown: string): string {
        return RemoveMarkdown(markdown, { gfm: true })
    }

    public static toSlug(string: string): string {
        return Slug(string);
    }

    private static link(href: string, title: string | null | undefined, text: string): string {
        const local = href.startsWith('#');


        console.log({ href, title, text});
        return `<a href="${local ? `documentation${href}` : href}" title="${title || text}" target="${local ? '_self' : '_blank'}" />${text}</a>`;
    }

    private static image(href: string, title: string | null, text: string): string {
        const base64img = require('base64-img');
        const src = Path.join(templatePath, href);
        const base64src = base64img.base64Sync(src);

        return `
            <figure class="figure docs-figure py-3 w-100 text-center">
                <img class="img-fluid" src="${base64src}" />
                <figcaption class="figure-caption mt-3">${title || text}</figcaption>
            </figure>
        `;
    }

    private static blockquote(quote: string): string {
        const isSeverity = (string: string): boolean => string.startsWith(':');
        const getSeverity = (string: string): string => isSeverity(string) ? string.substring(1) : 'info';
        const getIcon = (severity: string): string => {
            switch(getSeverity(severity)) {
                case 'warning':
                    return 'fa-bullhorn';
                    break;
                case 'success':
                    return 'fa-thumbs-up';
                    break;
                case 'danger':
                    return 'fa-triangle-exclamation';
                    break;
                default:
                    return 'fa-circle-info'
                    break;
            }
        }
        const getTitle = (severity: string): string => {
            switch(getSeverity(severity)) {
                case 'warning':
                    return 'Warning';
                    break;
                case 'success':
                    return 'Tip';
                    break;
                case 'danger':
                    return 'Danger';
                    break;
                default:
                    return 'Info'
                    break;
            }
        }
        const stripped = quote.replace('<p>', '').replace('</p>', '');
        const [severity, ...rest] = stripped.split(' ');

        return `
        <div class="callout-block callout-block-${getSeverity(severity)}">
                <div class="content">
                    <h4 class="callout-title">
                        <span class="callout-icon-holder me-1">
                            <i class="fas ${getIcon(severity)}"></i>
                        </span>
                        ${getTitle(severity)}
                    </h4>
                    ${isSeverity(severity) ? rest.join(' ') : stripped}
                </div>
            </div>
        `;
    }
}
