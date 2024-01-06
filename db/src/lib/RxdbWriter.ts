import FileWriter from "../abstract/FileWriter";
import Document from "../model/Document";
import Fs from 'fs';
import { program } from 'commander';
import { RxCollection, RxDatabase, RxJsonSchema, addRxPlugin, createRxDatabase } from 'rxdb';
import { getRxStorageMemory } from 'rxdb/plugins/storage-memory';
import { RxDBJsonDumpPlugin } from 'rxdb/plugins/json-dump';
import { RxDBDevModePlugin } from 'rxdb/plugins/dev-mode';
import Md5 from 'md5';
import Path from 'path';

export default class RxdbWriter extends FileWriter {
    public extension = 'json';
    private marked: Marked;

    public constructor(path: string, filename: string) {
      super(path, filename);
      const markedExtension: MarkedExtension = {
          async: false,
          breaks: true,
          gfm: true,
          renderer: {
            link: this.link,
            image: this.image,
            blockquote: this.blockquote,
          }
      };

      this.marked = new Marked(markedExtension);
    }

    public async write(document: Document): Promise<void> {
        let order = 1;
        addRxPlugin(RxDBJsonDumpPlugin);
        addRxPlugin(RxDBDevModePlugin);

        const rxdb: IGDBWrapperDatabase = await createRxDatabase<DocumentationDatabaseCollections>({
            name: 'igdbwdb',
            storage: getRxStorageMemory(),
        });

        const collections = await rxdb.addCollections({
            topics: {
                schema: this.getTopicJsonSchema(),
            },
            sections: {
                schema: this.getSectionJsonSchema()
            }
        });

        for (const topic of document.getTopics()) {
            const topicId = this.generateId(topic.getId(), topic.getSlug());

            await collections.topics.insert({
                id: topicId,
                order: order++,
                icon: topic.getIcon(),
                slug: topic.getSlug(),
                overview: topic.getOverview(),
                date: topic.getDate(),
                title: topic.getTitle(),
                stripped: this.toStripped(topic.getBody()),
                html: this.toHtml(topic.getBody()),
            });

            for (const section of topic.getSections()) {
                await collections.sections.insert({
                    id: this.generateId(section.getId(), section.getSlug()),
                    order: order++,
                    topicId,
                    parents: section.getParents().map(
                        (parentId: number) => {
                            const s: Section | undefined = topic.getSections().find((section: Section) => section.getId() === parentId);
                            return s ? this.generateId(s.getId(), s.getSlug()) : null;
                        }
                    ),
                    slug: section.getSlug(),
                    level: section.getLevel(),
                    title: section.getTitle(),
                    html: this.toHtml(section.getBody()),
                    stripped: this.toStripped(section.getBody()),
                })
            }
        }

        Fs.writeFileSync(
            this.getAbsolutePath(),
            program.opts().production ? JSON.stringify(await rxdb.exportJSON()) : JSON.stringify(await rxdb.exportJSON(), null, 2),
            { encoding: 'utf-8' },
        );

        await rxdb.remove();
    }

    private toHtml(markdown: string): string {
      return this.marked.parse(markdown) as string;
    }

    private toStripped(markdown: string): string {
      return RemoveMarkdown(markdown, { gfm: true })
    }

    private link(href: string, title: string | null | undefined, text: string): string {
      const local = href.startsWith('#');

      return `<a href="${local ? `documentation${href}` : href}" title="${title || text}${!local ? ' (external link)' : ''}" target="${local ? '_self' : '_blank'}" />${text}${!local ? ' <i class="fa-solid fa-arrow-up-right-from-square fa-2xs"></i>' : ''}</a>`;
    }

    private image(href: string, title: string | null, text: string): string {
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

    private blockquote(quote: string): string {
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

    private generateId(id: number, slug: string): string {
        return Md5(`${id}|${slug}`).substring(0,10);
    }

    private getSectionJsonSchema(): RxJsonSchema<SectionDocumentType> {
        return {
          version: 0,
          primaryKey: 'id',
          type: 'object',
          properties: {
            id: {
              type: 'string',
              maxLength: 10,
            },
            order: {
              type: 'number',
            },
            slug: {
              type: 'string',
            },
            title: {
              type: 'string',
            },
            stripped: {
              type: 'string',
            },
            html: {
              type: 'string',
            },
            topicId: {
              type: 'string',
            },
            parents: {
              type: 'array',
              items: {
                type: 'number',
              }
            },
            level: {
              type: 'number',
            }
          }
        };
      }

      private getTopicJsonSchema(): RxJsonSchema<TopicDocumentType> {
        return {
          version: 0,
          primaryKey: 'id',
          type: 'object',
          properties: {
            id: {
              type: 'string',
              maxLength: 10,
            },
            order: {
              type: 'number',
            },
            icon: {
              type: 'string',
            },
            slug: {
              type: 'string',
            },
            overview: {
              type: 'string',
            },
            date: {
              type: ['number', 'null'],
            },
            title: {
              type: 'string',
            },
            stripped: {
              type: 'string',
            },
            html: {
              type: 'string',
            },
          }
        };
      }
}

export type TopicCollectionMethods = {
    count: () => Promise<number>;
}

export type TopicCollection = RxCollection<TopicDocumentType, TopicDocumentMethods, TopicCollectionMethods>;

import { RxDocument } from "rxdb";
import Section from "../model/Section";
import { Marked, MarkedExtension } from "marked";
import { templatePath } from "..";
import StringR from "./StringR";
import RemoveMarkdown from "remove-markdown";

export type TopicDocumentType = {
    id: string;
    order: number;
    icon: string;
    slug: string;
    overview: string;
    date: number | null;
    title: string;
    stripped: string;
    html: string;
}

export type TopicDocumentMethods = {};

export type TopicDocument = RxDocument<TopicDocumentType, TopicDocumentMethods>;

export type SectionCollectionMethods = {
    count: () => Promise<number>;
}

export type SectionCollection = RxCollection<SectionDocumentType, SectionDocumentMethods, SectionCollectionMethods>;

export type SectionDocumentType = {
    id: string;
    order: number;
    topicId: string;
    parents: string[];
    slug: string;
    level: number;
    title: string;
    html: string;
    stripped: string;
}

export type SectionDocumentMethods = {};

export type SectionDocument = RxDocument<SectionDocumentType, SectionDocumentMethods>;

export type DocumentationDatabaseCollections = {
    topics: TopicCollection,
    sections: SectionCollection,
}

export type IGDBWrapperDatabase = RxDatabase<DocumentationDatabaseCollections>;
