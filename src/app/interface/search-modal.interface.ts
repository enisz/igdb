export interface ISearchResult {
    id: string;
    title: string;
    icon: string;
    sections: ISearchResultSection[]
}

export interface ISearchResultSection {
    id: string;
    title: string;
    slug: string;
    order: number;
}
