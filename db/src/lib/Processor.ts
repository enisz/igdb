import FrontMatter from 'front-matter';
import FileProcessor from "../abstract/FileProcessor";
import { HEADING_REGEXP } from "../constant";
import { IMarkdownAttributes } from "../interface/IMarkdownAttributes";
import Document from "../model/Document";
import File from '../model/File';
import Paragraph from "../model/Paragraph";
import Section from "../model/Section";
import Topic from "../model/Topic";
import StringR from "./StringR";

export default class Processor extends FileProcessor {
    private slugs: string[] = [];

    public process(files: File[]): Document {
        const document = new Document();
        const paragraphs: Paragraph[] = [];
        const attributeMap: {[key: number]: IMarkdownAttributes} = {};
        let paragraphId = 0;

        for (const file of files) {
            const { attributes, body } = FrontMatter<IMarkdownAttributes>(file.getContent());
            let tabset = false;

            for (const line of body.split('\n').map((line: string) => line.trimEnd())) {
                const match = line.match(HEADING_REGEXP);

                if (line.endsWith('{.tabset}')) tabset = true;

                if (match && !tabset) {
                    const id = paragraphId++;
                    const paragraph = new Paragraph(id, match[2].trim(), match[1].length);
                    paragraph.setDate(file.getDate());
                    paragraphs.push(paragraph);
                    attributeMap[id] = attributes;
                } else {
                    if (line.endsWith('{-}')) tabset = false;
                    paragraphs[paragraphs.length - 1].addLine(line);
                }
            }
        }

        this.mapParents(paragraphs);

        for (const paragraph of paragraphs) {
            if (paragraph.getLevel() === 1) {
                const { icon, overview } = attributeMap[paragraph.getId()];
                const topic = new Topic(paragraph.getId(), icon, overview, paragraph.getTitle(), paragraph.getContent(), paragraph.getDate());

                this.makeSlugUnique(topic);
                document.addTopic(topic);
            } else {
                const topic = document.getLastTopic();
                const section = new Section(paragraph.getId(), topic.getId(), paragraph.getParents(), paragraph.getLevel(), paragraph.getTitle(), paragraph.getContent());

                this.makeSlugUnique(section);
                topic.addSection(section);
            }
        }

        // this.validateAnchors(document);

        return document;
    }

    private validateAnchors(document: Document): void {
        const headings: string[] = [];

        for (const topic of document.getTopics()) {
            const topicTitle = topic.getTitle();

            if (!headings.includes(topicTitle)) {
                headings.push(topicTitle);
            }

            for (const section of topic.getSections()) {
                const sectionTitle = section.getTitle();

                if (!headings.includes(sectionTitle)) {
                    headings.push(sectionTitle);
                }
            }
        }

        for (const topic of document.getTopics()) {
            if (topic.hasLinks()) {

            }
            for (const section of topic.getSections()) {
            }
        }

    }

    private makeSlugUnique(item: Topic | Section): void {
        let counter = 2;
        const originalSlug = item.getSlug();
        while (this.slugs.includes(item.getSlug())) {
            item.setSlug(`${originalSlug}-${StringR.romanize(counter++).toLowerCase()}`);
        }

        this.slugs.push(item.getSlug());
    }

    private mapParents(paragraphs: Paragraph[]): void {
        for (let i = 0; i < paragraphs.length; i++) {
            const paragraph = paragraphs[i];
            let level = paragraph.getLevel();

            if(level > 2) {
                for(let j = i-1; j >= 0; j--) {
                    const compare = paragraphs[j];

                    if (level === 2) {
                        break;
                    } else if (compare.getLevel() === level - 1) {
                        paragraph.addParent(compare.getId());
                        level = compare.getLevel();
                    }
                }
            }
        }
    }

}
