import FileProcessor from "../abstract/FileProcessor";
import { IMarkdownAttributes } from "../interface/IMarkdownAttributes";
import Document from "../model/Document";
import File from '../model/File';
import FrontMatter from 'front-matter';
import Paragraph from "../model/Paragraph";
import Topic from "../model/Topic";
import Section from "../model/Section";
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
            const lines = body.split('\n');

            for (const line of lines) {
                const match = line.match(new RegExp('^(\#{1,6})(.*)'));

                if (match) {
                    const id = paragraphId++;
                    const paragraph = new Paragraph(id, match[2].trim(), match[1].length);
                    paragraph.setDate(file.getDate());
                    paragraphs.push(paragraph);
                    attributeMap[id] = attributes;
                } else {
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

        return document;
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
