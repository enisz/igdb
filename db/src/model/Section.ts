import { LINK_REGEXP, TABSET_REGEXP } from "../constant";
import StringR from "../lib/StringR";
import { Tabset } from "./Tabset";

export default class Section {
    private id: number;
    private topicId: number;
    private parents: number[];
    private slug: string;
    private level: number;
    private title: string;
    private body: string;
    private tabsets: Tabset[] = [];

    constructor(id: number, topicId: number, parents: number[], level: number, title: string, body: string) {
        this.id = id;
        this.topicId = topicId;
        this.parents = parents;
        this.slug = StringR.toSlug(title);
        this.level = level;
        this.title = title;
        this.body = body;
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

    public setSlug(newSlug: string): void {
        this.slug = newSlug;
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

    public setBody(body: string): void {
        this.body = body;
    }

    public hasLinks(): boolean {
        return !!this.body.match(LINK_REGEXP);
    }

    public getLinks(): RegExpMatchArray | null {
        return this.body.match(LINK_REGEXP);
    }

    public hasTabset(): boolean {
        return !!this.body.match(TABSET_REGEXP);
    }

    public addTabset(id: string, name: string): Tabset {
        const tabset = new Tabset(id, name);
        this.tabsets.push(tabset);

        return tabset;
    }

    public getTabsets(): Tabset[] {
        return this.tabsets;
    }
}
