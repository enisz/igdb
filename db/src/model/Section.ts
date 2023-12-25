import StringR from "../lib/StringR";

export default class Section {
    private id: number;
    private topicId: number;
    private parents: number[];
    private slug: string;
    private level: number;
    private title: string;
    private body: string;
    private html: string;
    private stripped: string;

    constructor(id: number, topicId: number, parents: number[], level: number, title: string, body: string) {
        this.id = id;
        this.topicId = topicId;
        this.parents = parents;
        this.slug = StringR.toSlug(title);
        this.level = level;
        this.title = title;
        this.body = body;

        this.html = StringR.toHtml(body);
        this.stripped = StringR.toStripped(body);
    }

    public getId(): number {
        return this.id;
    }

    public getTopicId(): number {
        return this.topicId;
    }

    public getParents(): number[] {
        return this.parents;
    }

    public getSlug(): string {
        return this.slug;
    }

    public getLevel(): number {
        return this.level;
    }

    public getTitle(): string {
        return this.title;
    }

    public getBody(): string {
        return this.body;
    }

    public getHtml(): string {
        return this.html;
    }

    public getStripped(): string {
        return this.stripped;
    }
}
