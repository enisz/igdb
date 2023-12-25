import Path from 'path';

export default class File {
    private path: string;
    private filename: string;
    private content: string;

    public constructor(path: string, filename: string, content: string) {
        this.path = path;
        this.filename = filename;
        this.content = content;
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

    public getAbsolutePath(): string {
        return Path.resolve(Path.join(this.path, this.filename));
    }
}
