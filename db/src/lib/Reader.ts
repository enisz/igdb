import ChildProcess from 'child_process';
import Path from 'path';
import FileReader from '../abstract/FileReader';
import File from '../model/File';
import FileHandler from './FileHandler';

export default class Reader extends FileReader {
    public constructor(path: string) {
        super(path);
    }

    public read(): File[] {
        return FileHandler
            .readDir(this.getPath())
            .filter((filename: string) => filename.endsWith('.md'))
            .map((filename: string) => {
                const content = FileHandler.readFile(Path.join(this.getPath(), filename));
                const path = Path.join(this.getPath(), filename);
                const timestamp = ChildProcess.execSync(`git log --format=%ct "${path}"`, { encoding: 'utf-8'}).trim().split('\n')[0];

                return new File(this.getPath(), filename, content, parseInt(timestamp, 10) || null);
            });
    }
}
