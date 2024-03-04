export default class Paragraph {
    private id: number;
    private title: string;
    private level: number;
    private parents: number[] = [];
    private content = '';
    private date: number | null;

    public constructor(id: number, title: string, level: number) {
        this.id = id;
        this.title = title;
        this.level = level;
        this.date = null;
    }

    public getId(): number {
        return this.id;
    }

    public getTitle(): string {
        return this.title;
    }

    public getLevel(): number {
        return this.level;
    }

    public getContent(): string {
        return this.content.trim();
    }

    public addLine(content: string): void {
        this.content += `${content}\n`;
    }

    public getDate(): number | null {
        return this.date;
    }

    public setDate(date: number | null): void {
        this.date = date;
    }

    public addParent(parentId: number): void {
        this.parents.unshift(parentId);
    }

    public getParents(): number[] {
        return this.parents;
    }

    public getParent(): number {
        return this.parents[0];
    }
}
