import ChildProcess from 'child_process';
import Fs from 'fs';
import Path from 'path';
import FileReader from '../abstract/FileReader';
import File from '../model/File';

export default class Reader extends FileReader {
    public constructor(path: string) {
        super(path);
    }

    public read(): File[] {
        return Fs
            .readdirSync(this.getPath(), { encoding: 'utf-8'})
            .filter((filename: string) => filename.endsWith('.md'))
            .map((filename: string) => {
                const content = Fs.readFileSync(Path.join(this.getPath(), filename), { encoding: 'utf-8'});
                const path = Path.join(this.getPath(), filename);
                const timestamp = ChildProcess.execSync(`git log --format=%ct "${path}"`, { encoding: 'utf-8'}).trim();

                return new File(this.getPath(), filename, content, parseInt(timestamp, 10) || null);
            });
    }
}
