<?php
namespace App\Libraries;

class Ais
{
    function make_latf($temp)
    {
        $temp = $temp & 0x07FFFFFF;
        if ($temp & 0x04000000) {
            $temp = $temp ^ 0x07FFFFFF;
            $temp += 1;
            $flat = ($temp / (60.0 * 10000.0));
            $flat *= -1.0;
        } else $flat = ($temp / (60.0 * 10000.0));
        return $flat;
    }

    function make_lonf($temp)
    {
        $temp = $temp & 0x0FFFFFFF;
        if ($temp & 0x08000000) {
            $temp = $temp ^ 0x0FFFFFFF;
            $temp += 1;
            $flon = ($temp / (60.0 * 10000.0));
            $flon *= -1.0;
        } else $flon = ($temp / (60.0 * 10000.0));
        return $flon;
    }

    function ascii_2_dec($chr)
    {
        $dec = ord($chr);
        return ($dec);
    }

    function asciidec_2_8bit($ascii)
    {
        if ($ascii < 48) {
        } else {
            if ($ascii > 119) {
            } else {
                if ($ascii > 87 && $ascii < 96) ;
                else {
                    $ascii = $ascii + 40;
                    if ($ascii > 128) {
                        $ascii = $ascii + 32;
                    } else {
                        $ascii = $ascii + 40;
                    }
                }
            }
        }
        return ($ascii);
    }

    function dec_2_6bit($dec)
    {
        return (substr(decbin($dec), -6));
    }

    function bindec($_str, $_start, $_size)
    {
        return bindec(substr($_str, $_start, $_size));
    }
    function binchar($_str, $_start, $_size)
    {
        $ais_chars = array(
            '', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
            'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']',
            '^', '_', ' ', '!', '\"', '#', '$', '%', '&', '\'',
            '(', ')', '*', '+', ',', '-', '.', '/', '0', '1',
            '2', '3', '4', '5', '6', '7', '8', '9', ':', ';',
            '<', '=', '>', '?'
        );
        $rv = '';
        if ($_size % 6 == 0) {
            $len = $_size / 6;
            for ($i = 0; $i < $len; $i++) {
                $offset = $i * 6;
                $rv .= $ais_chars[bindec(substr($_str, $_start + $offset, 6))];
            }
        }
        return $rv;
    }

    function decode_ais($_aisdata)
    {
        echo $_aisdata;
    }

    function process_ais_itu($_itu, $_len, $_filler)
    {
        $aisdata168 = '';

        $ais_nmea_array = str_split($_itu);
        foreach ($ais_nmea_array as $value) {
            $dec = $this->ascii_2_dec($value);
            $bit8 = $this->asciidec_2_8bit($dec);
            $bit6 = $this->dec_2_6bit($bit8);
            $aisdata168 .= $bit6;
        }
        return $this->decode_ais($aisdata168);
    }

    function process_ais_raw($rawdata)
    {
        static $pseq;
        static $cmsg_sid;
        static $itu;

        $filler = 0;
        $chksum = 0;

        $end = strrpos($rawdata, '*');
        if ($end === FALSE) return -1;

        $cs = substr($rawdata, $end + 1);
        if (strlen($cs) != 2) return -1;
        $dcs = (int)hexdec($cs);
        for ($alias = 1; $alias < $end; $alias++) {
            $chksum ^= ord($rawdata[$alias]);
        }
        if ($chksum == $dcs) {
            $pcs = explode(',', $rawdata);
            $num_seq = (int)$pcs[1];
            $seq = (int)$pcs[2];

            if ($pcs[3] == '') {
                $msg_sid = -1;
            } else {
                $msg_sid = (int)$pcs[3];
            }
            $ais_ch = $pcs[4];
            if ($num_seq < 1 || $num_seq > 9) {
                #echo "ERROR,INVALID_NUMBER_OF_SEQUENCES " . time() . " $rawdata\n";
                return false;
            } else if ($seq < 1 || $seq > 9) {
                #echo "ERROR,INVALID_SEQUENCES_NUMBER " . time() . " $rawdata\n";
                return false;
            } else if ($seq > $num_seq) {
                #echo "ERROR,INVALID_SEQUENCE_NUMBER_OR_INVALID_NUMBER_OF_SEQUENCES " . time() . " $rawdata\n";
                return false;
            } else {
                if ($seq == 1) {
                    $filler = 0;
                    $itu = "";
                    $pseq = 0;
                    $cmsg_sid = $msg_sid;
                }
                if ($num_seq > 1) {
                    if ($cmsg_sid != $msg_sid
                        || $msg_sid == -1
                        || ($seq - $pseq) != 1
                    ) {
                        $cmsg_sid = -1;
                        #echo "ERROR,INVALID_MULTIPART_MESSAGE " . time() . " $rawdata\n";
                        return false;
                    } else {
                        $pseq++;
                    }
                }

                $itu = $itu . $pcs[5];
                $filler += (int)$pcs[6][0];

                if ($num_seq == 1
                    || $num_seq == $pseq
                ) {
                    return $this->process_ais_itu($itu, strlen($itu), $filler);
                }
            }
        }
        return false;
    }

    function process_ais_buf($ibuf)
    {
        $cbuf = "" . $ibuf;
        $last_pos = 0;
        while (($start = strpos($cbuf, "VDM", $last_pos)) !== FALSE) {
            if (($end = strpos($cbuf, "\r\n", $start)) !== FALSE) {
                $tst = substr($cbuf, $start - 3, ($end - $start + 3));
                return $this->process_ais_raw($tst);
                $last_pos = $end + 1;
            } else break;
        }
        if ($last_pos > 0) $cbuf = substr($cbuf, $last_pos);
        if (strlen($cbuf) > 1024) $cbuf = "";
    }

    function mk_ais_lat($lat)
    {
        if ($lat < 0.0) {
            $lat = -$lat;
            $neg = true;
        } else $neg = false;
        $latd = intval($lat * 600000.0);
        if ($neg == true) {
            $latd = ~$latd;
            $latd += 1;
            $latd &= 0x07FFFFFF;
        }
        return $latd;
    }

    function mk_ais_lon($lon)
    {
        if ($lon < 0.0) {
            $lon = -$lon;
            $neg = true;
        } else $neg = false;
        $lond = intval($lon * 600000.0);
        if ($neg == true) {
            $lond = ~$lond;
            $lond += 1;
            $lond &= 0x0FFFFFFF;
        }
        return $lond;
    }

    function char2bin($name, $max_len)
    {
        $len = strlen($name);
        if ($len > $max_len) $name = substr($name, 0, $max_len);
        if ($len < $max_len) $pad = str_repeat('0', ($max_len - $len) * 6);
        else $pad = '';
        $rv = '';
        $ais_chars = array(
            '@' => 0, 'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9,
            'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13, 'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19,
            'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25, 'Z' => 26, '[' => 27, '\\' => 28, ']' => 29,
            '^' => 30, '_' => 31, ' ' => 32, '!' => 33, '\"' => 34, '#' => 35, '$' => 36, '%' => 37, '&' => 38, '\'' => 39,
            '(' => 40, ')' => 41, '*' => 42, '+' => 43, ',' => 44, '-' => 45, '.' => 46, '/' => 47, '0' => 48, '1' => 49,
            '2' => 50, '3' => 51, '4' => 52, '5' => 53, '6' => 54, '7' => 55, '8' => 56, '9' => 57, ':' => 58, ';' => 59,
            '<' => 60, '=' => 61, '>' => 62, '?' => 63
        );
        $_a = str_split($name);
        if ($_a) foreach ($_a as $_1) {
            if (isset($ais_chars[$_1])) $dec = $ais_chars[$_1];
            else $dec = 0;
            $bin = str_pad(decbin($dec), 6, '0', STR_PAD_LEFT);
            $rv .= $bin;
        }
        return $rv . $pad;
    }

    function mk_ais($_enc, $_part = 1, $_total = 1, $_seq = '', $_ch = 'A')
    {
        $len_bit = strlen($_enc);
        $rem6 = $len_bit % 6;
        $pad6_len = 0;
        if ($rem6) $pad6_len = 6 - $rem6;
        $_enc .= str_repeat("0", $pad6_len);
        $len_enc = strlen($_enc) / 6;
        $itu = '';
        for ($i = 0; $i < $len_enc; $i++) {
            $offset = $i * 6;
            $dec = bindec(substr($_enc, $offset, 6));
            if ($dec < 40) $dec += 48;
            else $dec += 56;
            $itu .= chr($dec);
        }

        $chksum = 0;
        $itu = "AIVDM,$_part,$_total,$_seq,$_ch," . $itu . ",0";
        $len_itu = strlen($itu);
        for ($i = 0; $i < $len_itu; $i++) {
            $chksum ^= ord($itu[$i]);
        }
        $hex_arr = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        $lsb = $chksum & 0x0F;
        if ($lsb >= 0 && $lsb <= 15) $lsbc = $hex_arr[$lsb];
        else $lsbc = '0';
        $msb = (($chksum & 0xF0) >> 4) & 0x0F;
        if ($msb >= 0 && $msb <= 15) $msbc = $hex_arr[$msb];
        else $msbc = '0';

        $itu = '!' . $itu . "*{$msbc}{$lsbc}\r\n";
        return $itu;
    }
}

?>
