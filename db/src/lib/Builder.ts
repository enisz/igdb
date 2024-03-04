import Fs from 'fs';
import FileProcessor from "../abstract/FileProcessor";
import FileReader from "../abstract/FileReader";
import FileWriter from "../abstract/FileWriter";
import Logger from "./Logger";

export default class Builder {
    protected logger = Logger.getLogger(Builder.name);
    private reader: FileReader;
    private processor: FileProcessor;
    private writer: FileWriter;

    public constructor(reader: FileReader, processor: FileProcessor, writer: FileWriter) {
        this.reader = reader;
        this.processor = processor;
        this.writer = writer;
    }

    public async build(): Promise<void> {
        const files = this.reader.read();
        const processed = this.processor.process(files);
        await this.writer.write(processed);

        const file = this.writer.getAbsolutePath();

        if (Fs.statSync(this.writer.getAbsolutePath()).isFile()) {
            console.log(`${this.writer.getExtension().toUpperCase()} exported succesfully!`);
            console.log(`File: ${this.writer.getAbsolutePath()}`);
            console.log(`Size: ${this.calculateFileSize(this.writer.getAbsolutePath())}\n`);
        }  else {
            throw new Error('no file is exported!')
        }
    }

    protected calculateFileSize(file: string): string {
        const units = ["kilobyte", "megabyte", "gigabyte"];
        const stat = Fs.statSync(file);
        const { size } = stat;

        if(stat.isFile()) {
            if(size < 1024) {
                return `${size} byte${size > 1 ? "s" : ""}`;
            } else {
                for(let i=0; i<units.length; i++) {
                    const result = size / (1024**(i+1));

                    if(result < 1) {
                        const actualSize = size / (1024**i);
                        return `${parseFloat(actualSize.toString()).toFixed(2)} ${units[i-1]}${actualSize > 1 ? "s" : ""}`;
                    }
                }

                return `${size} byte${size > 1 ? "s" : ""}`;
            }
        }

        throw new Error(`Cannot find ${file}!`);
    }
}
