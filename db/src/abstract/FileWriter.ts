import Document from "../model/Document";
import Path from 'path';

export default abstract class FileWriter {
    private path: string;
    private filename: string;
    public abstract extension: string;

    public constructor(path: string, filename: string) {
        this.path = path;
        this.filename = filename;
    }

    public getPath(): string {
        return this.path;
    }

    public getFilename(): string {
        return this.filename;
    }

    public getExtension(): string {
        return this.extension;
    }

    public getFilenameWithExtension(): string {
        return [this.filename, this.extension].join('.');
    }

    public getAbsolutePath(): string {
        return Path.join(this.getPath(), this.getFilenameWithExtension());
    }

    public abstract write(document: Document): Promise<void>
}
