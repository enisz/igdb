import { program } from 'commander';
import Md5 from 'md5';
import Path from 'path';
import { RxCollection, RxDatabase, RxJsonSchema, addRxPlugin, createRxDatabase } from 'rxdb';
import { RxDBDevModePlugin } from 'rxdb/plugins/dev-mode';
import { RxDBJsonDumpPlugin } from 'rxdb/plugins/json-dump';
import { getRxStorageMemory } from 'rxdb/plugins/storage-memory';
import FileWriter from "../abstract/FileWriter";
import Document from "../model/Document";

const base64img = require('base64-img');

export default class RxdbWriter extends FileWriter {
    private marked: Marked;

    public constructor(path: string, filename: string) {
      super(path, filename, 'json');
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

        this.processTabsets(document);

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
            const topicDocument = {
              id: topicId,
              order: order++,
              icon: topic.getIcon(),
              slug: topic.getSlug(),
              overview: topic.getOverview(),
              date: topic.getDate(),
              title: topic.getTitle(),
              stripped: this.toStripped(topic.getBody()),
              html: this.toHtml(topic.getBody()),
            };

            await collections.topics.insert(topicDocument);

            for (const section of topic.getSections()) {
              const sectionDocument = {
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
              };

              const tabsets = section.getTabsets();

              if (tabsets) {
                for (const tabset of tabsets) {

                  const context = {
                    id: tabset.getId(),
                    name: tabset.getName(),
                    tabs: tabset.getTabs().map((tab: Tab) => ({ id: tab.getId(), title: tab.getTitle(), content: this.toHtml(tab.getContent()), active: tab.getActive() })),
                  };

                  sectionDocument.html = sectionDocument.html.replace(`[.tabset#${tabset.getId()}]`, MustacheHandler.render('tabset', context));
                }
              }

              await collections.sections.insert(sectionDocument);
            }
        }

        FileHandler.writeFile(
          this.getAbsolutePath(),
          program.opts().production ? JSON.stringify(await rxdb.exportJSON()) : JSON.stringify(await rxdb.exportJSON(), null, 2),
        );

        await rxdb.remove();
    }

    private processTabsets(document: Document): void {
      for (const topic of document.getTopics()) {
        for (const section of topic.getSections()) {
          let tabsetCounter = 0;
          while (section.hasTabset()) {
            const match = section.getBody().match(TABSET_REGEXP);
            const tabsetName = match![1].trim();
            const tabsetId = Md5(`${topic.getId()}-${section.getId()}-${tabsetName}-${tabsetCounter++}`).substring(0, 10);
            const tabset = section.addTabset(tabsetId, tabsetName);

            let start = 0;
            let end = 0;
            let lineCounter = 0;
            let tabIdCounter = 0;
            const body = section.getBody().split('\n').map((line: string) => line.trimEnd());
            let tab;
            for (const line of body) {
              if (line.includes('#') && line.includes(tabset.getName()) && line.includes('{.tabset}')) {
                start = lineCounter;
                continue;
              }

              if (line.includes('#') && line.includes('{-}')) {
                end = lineCounter;
                break;
              }

              const headingMatcher = line.match(HEADING_REGEXP);

              if (headingMatcher) {
                const tabTitle = headingMatcher[2].trim();
                const tabId = Md5(`${tabsetId}-${tabIdCounter++}-${tabTitle}`).substring(0, 10);

                tab = tabset.addTab(tabId, tabTitle);
              } else {
                if (tab) {
                  tab.addContentLine(line);
                }
              }

              lineCounter++;
            }

            body.splice(start, end - start + 2, `[.tabset#${tabset.getId()}]`);
            section.setBody(body.join('\n'));
          }
        }
      }
    }

    private toHtml(markdown: string): string {
      return this.marked.parse(markdown) as string;
    }

    private toStripped(markdown: string): string {
      return RemoveMarkdown(markdown, { gfm: true })
    }

    private link(href: string, title: string | null | undefined, text: string): string {
      const context = {
        local: href.startsWith('#'),
        href,
        title: text.replace(/(<([^>]+)>)/gi, ''),
        // title: this.stripHtmlTags(text),
        text,
      };

      return MustacheHandler.render('link', context);
    }

    private image(href: string, title: string | null, text: string): string {
      const context = {
        src: base64img.base64Sync(Path.join(templatePath, href)),
        text,
      };

      return MustacheHandler.render('image', context);
    }

    private blockquote(quote: string): string {
      const getSeverity = (content: string): string => {
        const [severity, ...rest] = content.split(' ');
        return severity.startsWith(':') ? severity.substring(1) : 'info';
      };

      const getTitle = (severity: string): string => {
        switch(severity.toLowerCase()) {
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

      const getIcon = (severity: string): string => {
        switch(severity.toLowerCase()) {
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

      if (quote.startsWith('<p>')) {
        quote = quote.trim().substring(3, quote.length - 5);
      }

      const severity = getSeverity(quote);

      const context = {
        severity,
        icon: getIcon(severity),
        title: getTitle(severity),
        quote
      };

      return MustacheHandler.render('blockquote', context);
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

import { Marked, MarkedExtension } from "marked";
import RemoveMarkdown from "remove-markdown";
import { RxDocument } from "rxdb";
import { templatePath } from "..";
import { HEADING_REGEXP, TABSET_REGEXP } from '../constant';
import Section from "../model/Section";
import { Tab } from '../model/Tab';
import FileHandler from './FileHandler';
import MustacheHandler from './MustacheHandler';

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
