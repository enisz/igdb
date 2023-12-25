import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, merge, fromEvent } from 'rxjs';
import { IViewportBreakpoint, IViewportDimension } from '../interface/viewport.interface';

@Injectable({
  providedIn: 'root'
})
export class ViewportService {
  private breakpointSubject: BehaviorSubject<IViewportBreakpoint>;
  private viewportSubject: BehaviorSubject<IViewportDimension>;

  constructor() {
    this.breakpointSubject = new BehaviorSubject(this.calculateBreakpoint(window.innerWidth));
    this.viewportSubject = new BehaviorSubject(this.getViewportDimensions());

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
  }

  public getViewportObserable(): Observable<IViewportDimension> {
    return this.viewportSubject.asObservable();
  }

  public getBreakpointObservable(): Observable<IViewportBreakpoint> {
    return this.breakpointSubject.asObservable();
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
}
