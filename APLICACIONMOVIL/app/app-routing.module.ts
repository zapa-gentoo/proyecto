import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';

const routes: Routes = [
  {
    path: 'home',
    loadChildren: () => import('./home/home.module').then( m => m.HomePageModule)
  },
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full'
  },
  {
    path: 'testpage',
    loadChildren: () => import('./testpage/testpage.module').then( m => m.TestpagePageModule)
  },
  {
    path: 'll',
    loadChildren: () => import('./ll/ll.module').then( m => m.LlPageModule)
  },
  {
    path: 'activar',
    loadChildren: () => import('./activar/activar.module').then( m => m.ActivarPageModule)
  },
  {
    path: 'signal',
    loadChildren: () => import('./signal/signal.module').then( m => m.SignalPageModule)
  },
  {
    path: 'signal/:mac',
    loadChildren: () => import('./signal/signal.module').then( m => m.SignalPageModule)
  }
];

@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
