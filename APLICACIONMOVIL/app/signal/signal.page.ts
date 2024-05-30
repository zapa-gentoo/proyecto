
import { Component, OnInit } from '@angular/core';
import { NavController } from '@ionic/angular';
import { AlertController } from '@ionic/angular';  
import { HttpClient } from '@angular/common/http';  //Importamos modulo Http
import { ActivatedRoute,Router } from '@angular/router';
import {LoadingController} from '@ionic/angular';

@Component({
  selector: 'app-signal',
  templateUrl: './signal.page.html',
  styleUrls: ['./signal.page.scss'],
})
export class SignalPage implements OnInit {

  error:number = 0;
  mac:any = "";
  cliente:String ="";
  equipamiento:String ="";
  nodo:String="";
  signal:String="";
  data:any;
  id:any;

  private waitPresented:boolean=false;
  private alertPresented:boolean=false;

  constructor(public navCtrl:NavController, 
    private http: HttpClient, 
    public alertCtrl: AlertController, 
    private router: Router, 
    private route: ActivatedRoute,
    public loadingController: LoadingController) { 

      this.mac = this.route.params.subscribe(params => {
        this.mac = params['mac']; 
        if(this.mac!="") {
          this.startCalibration();
        } else {
          this.presentAlert("Se ha producido un error","Se debe introducir una dirección MAC previamente registrada");
          this.goBack();
        }
     });

     
    }



   
  ngOnInit() {


    
  }




  startCalibration():void {

      this.presentWait("show","...Recibiendo datos...",30000);
      
       this.id=setInterval(() => {
       this.http.get('https://www.sourcenet.es/PANEL/TECNICO/curl_signal_json.php?mac='+this.mac).subscribe((response:any) => {
         //console.log("recibimos info...error vale: " + this.error);
          
          if(response==null) {
            this.error=this.error+1;
          } else {
            this.error=0;
            this.equipamiento = response["equipamiento"];
            this.cliente = response["cliente"];
            this.nodo = response["nodo"];
            this.signal = response["signal"];
            this.presentWait("dismiss","",0);
          }

          if(this.error>=25) {                 //Tras 25 vueltas..si no se obtiene info..problema temporal en el server o mac no existente
            this.presentWait("dismiss","",0);
            this.presentAlert("Error","No se puede obtener los datos para la mac introducida");
            this.goBack();
            clearInterval(this.id);
          }
       });
     }, 1000);

  }


  goBack():void {
    clearInterval(this.id);    //Paramos el setInterval antes de salir
    this.router.navigate(['/activar']);
  }

  //Metodo para mostrar popup con error
  async presentAlert(headerr:string, mensaje:string,) {

    const alert = await this.alertCtrl.create({
      header: headerr,
      message: mensaje,
      buttons: ['OK'],
    });

      await alert.present();
     
  }




  async presentWait(action: string,mensaje: string, duracion: number) {

    var loading = await this.loadingController.create({
      message: mensaje,
      duration: duracion
      });


     switch(action) {

        case "show":
        if(this.waitPresented==false) {
           loading.present();
           this.waitPresented=true;
        }
        break;

        case "dismiss":
          if(this.waitPresented==true) {
            this.loadingController.dismiss();
            this.waitPresented=false;
          }
        break;

    }

  }



}
