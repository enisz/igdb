export interface IModalListGroup {
    title: string;
    icon: string;
    items: IModalListGroupItem[];
}

export interface IModalListGroupItem {
    id: number;
    title: string;
    slug?: string;
    clearable?: boolean;
}
