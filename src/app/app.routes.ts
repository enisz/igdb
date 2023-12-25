import { Routes } from '@angular/router';

export const routes: Routes = [
    {
        path: '',
        loadComponent: () => import('./page/home/home.component').then(mod => mod.HomeComponent)
    },
    {
        path: 'documentation',
        loadComponent: () => import('./page/documentation/documentation.component').then(mod => mod.DocumentationComponent)
    },
    {
        path: 'search',
        loadComponent: () => import('./page/search/search.component').then(mod => mod.SearchComponent)
    },
    {
        path: '404',
        loadComponent: () => import('./page/not-found/not-found.component').then(mod => mod.NotFoundComponent)
    },
    {
        path: '**',
        redirectTo: '404',
        pathMatch: 'full'
    },
];
