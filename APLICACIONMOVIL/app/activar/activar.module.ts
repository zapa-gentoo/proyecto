import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ActivarPageRoutingModule } from './activar-routing.module';

import { ActivarPage } from './activar.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ActivarPageRoutingModule
  ],
  declarations: [ActivarPage]
})
export class ActivarPageModule {}
