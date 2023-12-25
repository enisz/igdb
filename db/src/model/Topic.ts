import StringR from "../lib/StringR";
import Section from "./Section";

export default class Topic {
    private id: number;
    private icon: string;
    private slug: string;
    private overview: string;
    private date!: number;
    private title: string;
    private body: string;
    private stripped = '';
    private html = '';
    private sections: Section[] = [];

    constructor(id: number, icon: string, overview: string, title: string, body: string) {
        this.id = id;
        this.icon = icon;
        this.slug = StringR.toSlug(title);
        this.overview = overview;
        this.title = title;
        this.body = body;

        this.html = StringR.toHtml(body);
        this.stripped = StringR.toStripped(body);
    }

    public getId(): number {
        return this.id;
    }

    public getIcon(): string {
        return this.icon;
    }

    public getOverview(): string {
        return this.overview;
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

    public addSection(section: Section): void {
        this.sections.push(section);
    }

    public getSections(): Section[] {
        return this.sections;
    }

    public getSlug(): string {
        return this.slug;
    }

    public getDate(): number | null {
        return this.date;
    }
}
