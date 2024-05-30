import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ActivarPage } from './activar.page';

const routes: Routes = [
  {
    path: '',
    component: ActivarPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ActivarPageRoutingModule {}
