import Logger from "../lib/Logger";
import File from "../model/File";

export default abstract class FileReader {
    protected logger = Logger.getLogger(FileReader.name);
    private path: string;

    public constructor(path: string) {
        this.path = path;
    }

    public getPath(): string {
        return this.path;
    }

    public abstract read(): File[];
}
