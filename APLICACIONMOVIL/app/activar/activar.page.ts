
import { Component, OnInit } from '@angular/core';
import { NavController } from '@ionic/angular';
import { AlertController } from '@ionic/angular';  
import { HttpClient } from '@angular/common/http';  //Importamos modulo Http
import { NavigationExtras, Router } from '@angular/router';
import {LoadingController} from '@ionic/angular';

@Component({
  selector: 'app-activar',
  templateUrl: './activar.page.html',
  styleUrls: ['./activar.page.scss'],
})
export class ActivarPage implements OnInit {

  mac: String = "";
 public progress = 0;

  constructor(public navCtrl:NavController, 
    private http: HttpClient, 
    public alertCtrl: AlertController, 
    private router: Router, 
    public loadingController: LoadingController) { }

  ngOnInit() {
  }


  ActivarButton():void{

    
    let numero:number=1;
    this.presentWait("show","...Activando dispositivo...",90000);

    this.http.get('https://www.sourcenet.es/PANEL/TECNICO/curl_activar_json.php?mac='+this.mac).subscribe((response) => {
       this.presentWait("dismiss","",0);
       this.router.navigate(['/signal',this.mac]);
 });


    
     /*
    //this.presentWait("show","...Activando dispositivo...",90000);
    this.http.get('https://www.sourcenet.es/PANEL/TECNICO/curl_activar_json.php?mac='+this.mac).subscribe((response) => {

       var idinterval=setInterval(() => {
        if(numero<=100) {
          //this.presentWait("dismiss","",0);
          //this.presentWait("show","...Activando dispositivo... "+numero+"%",90000);
          numero=numero+1;
        } else {
          //this.presentWait("dismiss","",0);
          this.router.navigate(['/signal',this.mac]);
          clearInterval(idinterval);  
        }
      }, 9);

      

    }); */


  }


  



  async presentWait(action: string,mensaje: string, duracion: number) {

    var loading = await this.loadingController.create({
      message: mensaje,
      duration: duracion
      });


     switch(action) {

        case "show":
         loading.present();
        break;

        case "dismiss":
         this.loadingController.dismiss();
        break;

    }

  }



 



}
