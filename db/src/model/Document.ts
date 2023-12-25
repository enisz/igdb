import Topic from "./Topic";

export default class Document {
    private topics: Topic[] = [];

    public addTopic(topic: Topic): void {
        this.topics.push(topic);
    }

    public getTopics(): Topic[] {
        return this.topics;
    }

    public getLastTopic(): Topic {
        return this.topics[this.topics.length - 1];
    }
}
