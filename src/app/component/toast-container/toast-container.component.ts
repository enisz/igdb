import { animate, style, transition, trigger } from '@angular/animations';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';
import { IToast } from '../../interface/toast.interface';
import { IViewportBreakpoint } from '../../interface/viewport.interface';
import { ToastService } from '../../service/toast.service';
import { ViewportService } from '../../service/viewport.service';
import { ToastComponent } from './toast/toast.component';

@Component({
  selector: 'app-toast-container',
  standalone: true,
  imports: [ToastComponent],
  templateUrl: './toast-container.component.html',
  styleUrl: './toast-container.component.scss',
  animations: [
    trigger('fade', [
      transition(':enter', [
        style({ position: 'relative', top: '56px' }),
        animate(150, style({ top: '0px', minHeight: '50px' })),
      ]),
      transition(':leave', [
        style({ position: 'relative', left: '0%' }),
        animate(150, style({ left: '100%' }))
      ]),
    ])
  ],
})
export class ToastContainerComponent implements OnInit, OnDestroy {
  public isSm = false;
  public toasts: {[key: number]: IToast} = {};
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly toastService: ToastService,
    private readonly viewportService: ViewportService,
  ) {}

  public ngOnInit(): void {
    this.subscriptions.push(
      this.toastService.getToastObservable().subscribe(
        (toast: {[key: number]: IToast}) => this.toasts = toast
      )
    );

    this.subscriptions.push(
      this.viewportService.getBreakpointObservable().subscribe(
        (breakpoint: IViewportBreakpoint) => this.isSm = breakpoint === 'sm'
      )
    );
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  public getToasts(): (IToast & { id: number })[] {
    const toasts: (IToast & { id: number })[] = [];

    for (const [key, value] of Object.entries(this.toasts)) {
      const {message, type, timeout} = value;
      toasts.push({ id: parseInt(key), message, type, timeout });
    }

    return toasts;
  }

  public removeToast(id: number): void {
    delete this.toasts[id];
  }
}