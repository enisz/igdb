import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, fromEvent } from 'rxjs';
import { IScrollPosition, IViewportBreakpoint, IViewportDimension } from '../interface/viewport.interface';

@Injectable({
  providedIn: 'root'
})
export class ViewportService {
  private breakpointSubject: BehaviorSubject<IViewportBreakpoint>;
  private viewportSubject: BehaviorSubject<IViewportDimension>;
  private scrollSubject: BehaviorSubject<IScrollPosition>;

  constructor() {
    this.breakpointSubject = new BehaviorSubject(this.calculateBreakpoint(window.innerWidth));
    this.viewportSubject = new BehaviorSubject(this.getViewportDimensions());
    this.scrollSubject = new BehaviorSubject(this.getScrollPosition());

    fromEvent(window, 'resize').subscribe(
      () => {
        const { width, height } = this.getViewportDimensions();
        const breakpoint = this.calculateBreakpoint(width);

        this.viewportSubject.next({ width, height });
        if (this.breakpointSubject.getValue() !== breakpoint) {
          this.breakpointSubject.next(breakpoint);
        }
      }
    );

    fromEvent(window, 'scroll').subscribe(
      () => this.scrollSubject.next(this.getScrollPosition())
    );
  }

  public getViewportValue(): IViewportDimension {
    return this.viewportSubject.value;
  }

  public getViewportObserable(): Observable<IViewportDimension> {
    return this.viewportSubject.asObservable();
  }

  public getBreakpointValue(): IViewportBreakpoint {
    return this.breakpointSubject.value;
  }

  public getBreakpointObservable(): Observable<IViewportBreakpoint> {
    return this.breakpointSubject.asObservable();
  }

  public getScrollObservable(): Observable<IScrollPosition> {
    return this.scrollSubject.asObservable();
  }

  private calculateBreakpoint(width: number): IViewportBreakpoint {
    if (width >= 1400) {
      return 'xxl';
    } else if (width >= 1200) {
      return 'xl';
    } else if (width >= 992) {
      return 'lg';
    } else if (width >= 768) {
      return 'md';
    } else if (width >= 576) {
      return 'sm';
    } else {
      return 'xs';
    };
  }

  private getViewportDimensions(): IViewportDimension {
    return {
      width: window.innerWidth,
      height: window.innerHeight,
    }
  }

  private getScrollPosition(): IScrollPosition {
    const body = document.body;
    const html = document.documentElement;

    const documentHeight = Math.max(
      body.scrollHeight,
      body.offsetHeight,
      html.clientHeight,
      html.scrollHeight,
      html.offsetHeight,
    );

    const viewportTop = window.scrollY;
    const viewportBottom = this.getViewportDimensions().height;
    const scrollPercentage = Math.ceil(viewportTop / documentHeight * 100);

    return {
      documentHeight,
      viewportTop,
      viewportBottom,
      scrollPercentage,
    }
  }
}
