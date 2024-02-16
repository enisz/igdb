import Path from 'path';
import Logger from "../lib/Logger";
import Document from "../model/Document";

export default abstract class FileWriter {
    protected logger = Logger.getLogger(FileWriter.name);
    private path: string;
    private filename: string;
    private extension: string;

    public constructor(path: string, filename: string, extension: string) {
        this.path = path;
        this.filename = filename;
        this.extension = extension;
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
