import Path from 'path';

export default class File {
    private path: string;
    private filename: string;
    private content: string;
    private date: number | null;

    public constructor(path: string, filename: string, content: string, date: number | null) {
        this.path = path;
        this.filename = filename;
        this.content = content;
        this.date = date;
    }

    public getPath(): string {
        return this.path;
    }

    public getFilename(): string {
        return this.filename;
    }

    public getContent(): string {
        return this.content;
    }

    public getDate(): number | null {
        return this.date;
    }

    public getAbsolutePath(): string {
        return Path.resolve(Path.join(this.path, this.filename));
    }
}
