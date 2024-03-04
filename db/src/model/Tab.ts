export class Tab {
    private id: string;
    private title: string;
    private active: boolean;
    private content: string;

    public constructor(id: string, title: string, active: boolean = false) {
        this.id = id;
        this.title = title;
        this.active = active;
        this.content = '';
    }

    public getId(): string {
        return this.id;
    }

    public getTitle(): string {
        return this.title;
    }

    public getContent(): string {
        return this.content;
    }

    public addContentLine(line: string): void {
        this.content += line + '\n';
    }

    public setActive(active: boolean): void {
        this.active = active;
    }

    public getActive(): boolean {
        return this.active;
    }
}
