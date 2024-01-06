import StringR from "../lib/StringR";
import Section from "./Section";

export default class Topic {
    private id: number;
    private icon: string;
    private slug: string;
    private overview: string;
    private date: number | null;
    private title: string;
    private body: string;
    private sections: Section[] = [];

    constructor(id: number, icon: string, overview: string, title: string, body: string, date: number | null) {
        this.id = id;
        this.icon = icon;
        this.slug = StringR.toSlug(title);
        this.overview = overview;
        this.title = title;
        this.body = body;
        this.date = date;
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

    public addSection(section: Section): void {
        this.sections.push(section);
    }

    public getSections(): Section[] {
        return this.sections;
    }

    public getSlug(): string {
        return this.slug;
    }

    public setSlug(newSlug: string): void {
        this.slug = newSlug;
    }

    public getDate(): number | null {
        return this.date;
    }
}
