<?php

namespace App\Libraries;

use App\Libraries\Ais;

class MyAis extends Ais
{
    // AIS information https://gpsd.gitlab.io/gpsd/AIVDM.html
    // Call debug by process_ais_itu(...) method
    public function decode_ais($_aisdata)
    {
        $ro = new \stdClass();

        $ro->id = $this->bindec($_aisdata, 0, 6);
        $ro->repeat = $this->bindec($_aisdata, 6, 2);
        $ro->mmsi = $this->bindec($_aisdata, 8, 30);
        if ($ro->id >= 1 && $ro->id <= 3) {
            $ro->navstatus = $this->bindec($_aisdata, 38, 4);
            $ro->turn = $this->bindec($_aisdata, 42, 8);
            $ro->sog = $this->bindec($_aisdata, 50, 10) / 10;
            $ro->accuracy = $this->bindec($_aisdata, 60, 1);
            $ro->lon = $this->make_lonf($this->bindec($_aisdata, 61, 28));
            $ro->lat = $this->make_latf($this->bindec($_aisdata, 89, 27));
            $ro->cog = $this->bindec($_aisdata, 116, 12) / 10;
            $ro->heading = $this->bindec($_aisdata, 128, 9);
            $ro->second = $this->bindec($_aisdata, 137, 6);
            $ro->maneuver = $this->bindec($_aisdata, 143, 2);
            $ro->raim = $this->bindec($_aisdata, 148, 1);
            $ro->radio = $this->bindec($_aisdata, 149, 19);
            $ro->cls = 1; // class A
        } else if ($ro->id == 5) {
            $ro->aisversion = $this->bindec($_aisdata, 38, 2);
            $ro->imo = $this->bindec($_aisdata, 40, 30);
            $ro->callsign = $this->binchar($_aisdata, 70, 42);
            $ro->name = $this->binchar($_aisdata, 112, 120);
            $ro->shiptype = $this->bindec($_aisdata, 232, 8);
            $ro->a = $this->bindec($_aisdata, 240, 9); #Dimension to Bow
            $ro->b = $this->bindec($_aisdata, 249, 9); #Dimension to Stern
            $ro->c = $this->bindec($_aisdata, 258, 6); #Dimension to Port
            $ro->d = $this->bindec($_aisdata, 264, 6); #Dimension to Starboard
            $ro->epfd = $this->bindec($_aisdata, 270, 4); #Position Fix Type
            $ro->month = $this->bindec($_aisdata, 274, 4);
            $ro->day = $this->bindec($_aisdata, 278, 5);
            $ro->hour = $this->bindec($_aisdata, 283, 5);
            $ro->minute = $this->bindec($_aisdata, 288, 6);
            $ro->draught = $this->bindec($_aisdata, 294, 8);
            $ro->destination = $this->binchar($_aisdata, 302, 120);
            $ro->dte = $this->bindec($_aisdata, 244, 1);
            $ro->cls = 1; // class A
        } else if ($ro->id == 8) {
            $ro->seqno = $this->bindec($_aisdata, 38, 2);
            $ro->dest_mmsi = $this->bindec($_aisdata, 40, 30);
            $ro->retransmit = $this->bindec($_aisdata, 70, 1);
            $ro->dac = $this->bindec($_aisdata, 72, 10);
            $ro->fid = $this->bindec($_aisdata, 82, 6);
            $ro->lastport = $this->bindec($_aisdata, 88, 30);
            $ro->lmonth = $this->bindec($_aisdata, 118, 4);
            $ro->lday = $this->bindec($_aisdata, 122, 5);
            $ro->lhour = $this->bindec($_aisdata, 127, 5);
            $ro->lminute = $this->bindec($_aisdata, 132, 6);
            $ro->nextport = $this->bindec($_aisdata, 138, 30);
            $ro->nmonth = $this->bindec($_aisdata, 168, 4);
            $ro->nday = $this->bindec($_aisdata, 172, 5);
            $ro->nhour = $this->bindec($_aisdata, 177, 5);
            $ro->nminute = $this->bindec($_aisdata, 182, 6);
            $ro->dangerous = $this->bindec($_aisdata, 188, 120);
            $ro->imdcat = $this->bindec($_aisdata, 308, 24);
            $ro->unid = $this->bindec($_aisdata, 332, 13);
            $ro->amount = $this->bindec($_aisdata, 345, 10);
            $ro->unit = $this->bindec($_aisdata, 355, 2);
        } else if ($ro->id == 15) {
            $ro->mmsi1 = $this->bindec($_aisdata, 40, 30);
            $ro->type1_1 = $this->bindec($_aisdata, 70, 6);
            $ro->offset1_1 = $this->bindec($_aisdata, 76, 12);
            $ro->type1_2 = $this->bindec($_aisdata, 90, 6);
            $ro->offset1_2 = $this->bindec($_aisdata, 96, 12);
            $ro->mmsi2 = $this->bindec($_aisdata, 110, 30);
            $ro->type2_1 = $this->bindec($_aisdata, 140, 6);
            $ro->offset2_1 = $this->bindec($_aisdata, 146, 12);
        } else if ($ro->id == 18) {
            $ro->sog = $this->bindec($_aisdata, 46, 10) / 10;
            $ro->accuracy = $this->bindec($_aisdata, 56, 1);
            $ro->lon = $this->make_lonf($this->bindec($_aisdata, 57, 28));
            $ro->lat = $this->make_latf($this->bindec($_aisdata, 85, 27));
            $ro->cog = $this->bindec($_aisdata, 112, 12) / 10;
            $ro->heading = $this->bindec($_aisdata, 124, 9);
            $ro->cls = 2; // class B
        } else if ($ro->id == 19) {
            $ro->sog = $this->bindec($_aisdata, 46, 10) / 10;
            $ro->lon = $this->make_lonf($this->bindec($_aisdata, 61, 28));
            $ro->lat = $this->make_latf($this->bindec($_aisdata, 89, 27));
            $ro->cog = $this->bindec($_aisdata, 112, 12) / 10;
            $ro->accuracy = $this->bindec($_aisdata, 56, 1);
            $ro->name = $this->binchar($_aisdata, 143, 120);
            $ro->cls = 2; // class B
        } else if ($ro->id == 24) {
            $ro->partno = $this->bindec($_aisdata, 38, 2);
            if ($ro->partno == 0) { #Part A
                $ro->name = $this->binchar($_aisdata, 40, 120);
            } else { #Part B
                $ro->shiptype = $this->bindec($_aisdata, 40, 8);
                $ro->vendorid = $this->bindec($_aisdata, 48, 18);
                $ro->model = $this->bindec($_aisdata, 66, 4);
                $ro->serial = $this->bindec($_aisdata, 70, 20);
                $ro->callsign = $this->bindec($_aisdata, 90, 42);
                $ro->a = $this->bindec($_aisdata, 132, 9); #Dimension to Bow
                $ro->b = $this->bindec($_aisdata, 141, 9); #Dimension to Stern
                $ro->c = $this->bindec($_aisdata, 150, 6); #Dimension to Port
                $ro->d = $this->bindec($_aisdata, 156, 6); #Dimension to Starboard
            }
            $ro->cls = 2; // class B
        } else if ($ro->id == 27) {
            $ro->accuracy = $this->bindec($_aisdata, 38, 1);
            $ro->raim = $this->bindec($_aisdata, 39, 1);
            $ro->status = $this->bindec($_aisdata, 40, 4);
            $ro->lon = $this->make_lonf($this->bindec($_aisdata, 44, 18))*1000;
            $ro->lat = $this->make_latf($this->bindec($_aisdata, 62, 17))*1000;
            $ro->speed = $this->bindec($_aisdata, 79, 6);
            $ro->course = $this->bindec($_aisdata, 85, 9);
            $ro->gnss = $this->bindec($_aisdata, 94, 1);
        }

        return $ro;
    }

    public function encode_ais($ro, $signalId = 0)
    {
        $ais = new Ais();

        $enc = '';
        $enc .= str_pad(decbin($signalId), 6, 0, STR_PAD_LEFT);
        $enc .= str_pad(decbin(0), 2, 0, STR_PAD_LEFT);
        $enc .= str_pad(decbin($ro->mmsi), 30, 0, STR_PAD_LEFT);
        if ($signalId >= 1 && $signalId <= 3) {
            $enc .= str_pad(decbin(0), 12, 0, STR_PAD_LEFT);
            $enc .= str_pad(decbin(intval($ro->sog * 10)), 10, 0, STR_PAD_LEFT);
            $enc .= str_pad(decbin($ais->mk_ais_lon($ro->lon)), 29, 0, STR_PAD_LEFT);
            $enc .= str_pad(decbin($ais->mk_ais_lat($ro->lat)), 27, 0, STR_PAD_LEFT);
            $enc .= str_pad(decbin(intval($ro->cog * 10)), 11, 0, STR_PAD_LEFT);
            // class A
        } else if ($signalId == 5) {
            //$imo = bindec(substr($_aisdata, 40, 30));
            //$cs = $this->binchar($_aisdata, 70, 42);
            //$ro->name = $this->binchar($ro->name, 112, 120);
            //$ro->cls = 1; // class A
        } else if ($signalId == 18) {
            //$ro->cog = bindec(substr($_aisdata, 112, 12)) / 10;
            //$ro->sog = bindec(substr($_aisdata, 46, 10)) / 10;
            //$ro->lon = $this->make_lonf(bindec(substr($_aisdata, 57, 28)));
            //$ro->lat = $this->make_latf(bindec(substr($_aisdata, 85, 27)));
            //$ro->cls = 2; // class B
        } else if ($signalId == 19) {
            //$ro->cog = bindec(substr($_aisdata, 112, 12)) / 10;
            //$ro->sog = bindec(substr($_aisdata, 46, 10)) / 10;
            //$ro->lon = $this->make_lonf(bindec(substr($_aisdata, 61, 28)));
            //$ro->lat = $this->make_latf(bindec(substr($_aisdata, 89, 27)));
            //$ro->name = $this->binchar($_aisdata, 143, 120);
            //$ro->cls = 2; // class B
        } else if ($signalId == 24) {
            $enc .= str_pad(decbin(0), 2, 0, STR_PAD_LEFT);
            $enc .= $ais->char2bin($ro->name, 20);
            //$ro->cls = 2; // class B
        }
        $enc .= str_pad(decbin(0), 41, 0, STR_PAD_LEFT);

        return $this->mk_ais($enc)."\r\n";
    }

}