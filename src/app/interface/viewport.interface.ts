export interface IViewportDimension {
    width: number;
    height: number;
}

export type IViewportBreakpoint = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl';

export interface IScrollPosition {
    documentHeight: number;
    viewportTop: number;
    viewportBottom: number;
    scrollPercentage: number;
}
