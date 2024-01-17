import { Component, OnDestroy, OnInit } from '@angular/core';
import { ToastComponent } from './toast/toast.component';
import { IToast } from '../../interface/toast.interface';
import { ToastService } from '../../service/toast.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-toast-container',
  standalone: true,
  imports: [ToastComponent],
  templateUrl: './toast-container.component.html',
  styleUrl: './toast-container.component.scss'
})
export class ToastContainerComponent implements OnInit, OnDestroy {
  public toasts: {[key: number]: IToast} = {};
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly toastService: ToastService,
  ) {}

  public ngOnInit(): void {
    this.subscriptions.push(
      this.toastService.getToastObservable().subscribe(
        (toast: {[key: number]: IToast}) => this.toasts = toast
      )
    )
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
