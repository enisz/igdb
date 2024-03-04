import Logger from '../lib/Logger';
import Document from '../model/Document';
import File from '../model/File';

export default abstract class FileProcessor {
    protected logger = Logger.getLogger(FileProcessor.name);
    public abstract process(files: File[]): Document;
}
