#!/usr/bin/env python
# thx Voidless
# -*- coding: utf-8 -*-
import fnmatch
import os

######################################
## НАСТРОЙКИ СКРИПТА
confdir = "/etc/vz/conf/"
rulefile = "/etc/vz/shaper/rules.sh"

# шейпятся только эти тарифы
table_band = {
    #ORIGIN_SAMPLE : канал
    "SSD8": "100mbit",
    "SSD7": "100mbit",
    "SSD6": "100mbit",
    "SSD5": "100mbit",
    "SSD4": "100mbit",
    "SSD3": "100mbit",
    "SSD2": "100mbit",
    "SSD1": "100mbit"
}

######################################
## ШАБЛОН СКРИПТА НАСТРОЙКИ ШЕЙПЕРА

fileheader = """#!/bin/bash
# shaper configuration file

##### ЧИСТКА ОТ СТАРЫХ ПРАВИЛ #####
tc qdisc del dev eth0 root
tc -s qdisc ls dev eth0

tc qdisc del dev venet0 root
tc -s qdisc ls dev venet0
##### ЧИСТКА ОТ СТАРЫХ ПРАВИЛ #####
"""

# eth0

eth0header = """
DEV=eth0
tc qdisc del dev $DEV root 2>/dev/null
tc qdisc add dev $DEV root handle 1: htb default 1 r2q 3000
tc class add dev $DEV parent 1: classid 1:1 htb rate 1000mbit burst 10mb
"""

eth0template = """
# vzid {VPSID} tarif {BANDWIDTH} ip {IP}

tc class add dev $DEV parent 1:1 classid 1:{CLASS} htb rate {BANDWIDTH} ceil {BANDWIDTH} burst 1mb
tc qdisc add dev $DEV parent 1:{CLASS} handle {CLASS}: sfq perturb 5 #quantum 5000b
"""

eth0match = """
tc filter add dev $DEV protocol ip parent 1:0 prio 1 u32 match ip src {IP} flowid 1:{CLASS}
"""

# venet0

venet0header = """
DEV=venet0
tc qdisc del dev $DEV root 2>/dev/null
tc qdisc add dev $DEV root handle 1: htb default 1 r2q 3000
tc class add dev $DEV parent 1: classid 1:1 htb rate 1000mbit burst 10mb
"""

venet0template = """
# vzid {VPSID} tarif {BANDWIDTH} ip {IP}

tc class add dev $DEV parent 1:1 classid 1:{CLASS} htb rate {BANDWIDTH} ceil {BANDWIDTH} burst 1mb
tc qdisc add dev $DEV parent 1:{CLASS} handle {CLASS}: sfq perturb 5 #quantum 5000b
"""

venet0match = """
tc filter add dev $DEV protocol ip parent 1:0 prio 1 u32 match ip dst {IP} flowid 1:{CLASS}
"""

######################################
## КОД СКРИПТА
# получает список и настройки виртуалок из папки конфигов OpenVZ
def getvz():
    vzlist = []

    for file in os.listdir(confdir):
        if fnmatch.fnmatch(file, '*.conf') and file != "0.conf":
            #берем список конфигов
            vzid = file.split(".",1)[0]
            vztarif = None
            vzip = None

            #открываем файл, узнаем название тарифа
            with open(confdir + file) as f:
                for line in f:
                    if line[0]=="#":
                        continue

                    ar = line.split("=")

                    if ar[0] == "ORIGIN_SAMPLE":
                        vztarif = ar[1].replace('"', '').strip()
                        continue

                    if ar[0] == "IP_ADDRESS":
                        vzip = ar[1].replace('"', '').strip().split(" ")
                        continue

            #если в файле не написан тариф, или он левый, ничего не делаем
            if vztarif == None or vzip == None or vztarif not in table_band:
                continue
            
            # в список записываем инфу про все vps
            vzlist.append((vzid, table_band[vztarif], vzip))
    return vzlist

# записывает команды настройки шейпера в sh файл
def genconf(vzlist):
    with open(rulefile,"w+") as f:

        # file header
        f.write(fileheader)

        # eth0 rules
        f.write(eth0header)

        shaper_id = 100
        for vz in vzlist:
            f.write(eth0template
                        .replace("{VPSID}",vz[0])
                        .replace("{CLASS}",str(shaper_id))
                        .replace("{BANDWIDTH}",vz[1])
                        .replace("{IP}",str(vz[2])))
            for ip in vz[2]:
                f.write(eth0match
                            .replace("{VPSID}",vz[0])
                            .replace("{CLASS}",str(shaper_id))
                            .replace("{IP}",ip))
            shaper_id += 1

        # venet0 rules
        f.write(venet0header)

        shaper_id = 100
        for vz in vzlist:
            f.write(venet0template
                        .replace("{VPSID}",vz[0])
                        .replace("{CLASS}",str(shaper_id))
                        .replace("{BANDWIDTH}",vz[1])
                        .replace("{IP}",str(vz[2])))
            for ip in vz[2]:
                f.write(venet0match
                            .replace("{VPSID}",vz[0])
                            .replace("{CLASS}",str(shaper_id))
                            .replace("{IP}",ip))
            shaper_id += 1

    # make in runnable
    os.chmod(rulefile, 0755)

## основной код скрипта. выполнение функций
# получили список VPSок
vzlist = getvz()

# сгенерировали скрипт с правилами
genconf(vzlist)

# применили правила
os.system(rulefile)
