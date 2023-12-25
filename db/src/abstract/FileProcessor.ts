import Document from '../model/Document';
import File from '../model/File';

export default abstract class FileProcessor {
    public abstract process(files: File[]): Document;
}
