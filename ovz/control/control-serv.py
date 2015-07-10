#!/usr/bin/env python
# -*- coding: utf-8 -*-
import subprocess
import hashlib
from SocketServer import ThreadingMixIn
from BaseHTTPServer import HTTPServer, BaseHTTPRequestHandler
from time import gmtime, strftime
import string
import random

listen = "x.x.x.x"
port = 25001
allowedIps = ["x.x.x.x", "y.y.y.y"]
secretKey = ""
logFile = "/etc/vz/control/command.log"
errlogFile = "/etc/vz/control/error.log"


dispatcher = {
    "startServer": 1, #params: id
    "stopServer": 1, #params: id
    "restartServer": 1, #params: id
    "getStatus": 1, #params: id
    "getStatusEx": 1, #params: id
    "createServer": 7, #params: id, ip, tarif, OS, pass, key, hostname
    "changeTarif": 2, #params: id, newtarif
}


class ReqHandler(BaseHTTPRequestHandler):

    def changeTarif(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        for char in param[1]:
            if char not in (string.ascii_lowercase + string.ascii_uppercase + string.digits + '.@-'): # [a-zA-z0-9.@-]
                return self.err("incorrect newtarif")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        if ( output[2] != 'exist' ):
            return self.err("Err: cannot changeTarif, vzid does not exist")
        elif ( output[4] != 'running' ):
            return self.err("Err: cannot changeTarif, vzid is not running")
        else:
            output = subprocess.call(['vzctl', 'set', param[0], '--applyconfig', param[1], '--save'])
            output = subprocess.call(['/etc/vz/shaper/gen.py'])
            self.response(output) # 0 = success

    def createServer(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        for char in param[1]:
            if char not in (string.digits + '.'): # [0-9.]
                return self.err("incorrect ip")
        for char in param[2]:
            if char not in (string.ascii_lowercase + string.ascii_uppercase + string.digits + '.@-'): # [a-zA-z0-9.@-]
                return self.err("incorrect tarif")
        for char in param[3]:
            if char not in (string.ascii_lowercase + string.ascii_uppercase + string.digits + '.@-'): # [a-zA-z0-9.@-]
                return self.err("incorrect OS")
        for char in param[4]:
            if char not in (string.ascii_lowercase + string.ascii_uppercase + string.digits + '.@-'): # [a-zA-z0-9.@-]
                return self.err("incorrect pass")
        for char in param[5]:
            if char not in (string.ascii_lowercase + string.ascii_uppercase + string.digits + '.@-'): # [a-zA-z0-9.@-]
                return self.err("incorrect key")
        for char in param[6]:
            if char not in (string.ascii_lowercase + string.ascii_uppercase + string.digits + '.'): # [a-zA-z0-9.]
                return self.err("incorrect hostname")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        if ( output[2] != 'deleted' ):
            return self.err("Err: cannot createServer, vzid already exist")
        elif ( output[4] != 'down' ):
            return self.err("Err: cannot createServer, vzid already exist")
        else:
            output = subprocess.call(['vzctl', 'create', param[0], '--ostemplate', param[3], '--config', param[2], '--ipadd', param[1], '--hostname', param[6]])
            if ( output != 0 ):
                return self.err('create '+str(output))
            output = subprocess.call(['vzctl', 'start', param[0]])
            if ( output != 0 ):
                return self.err('start '+str(output))
            #newpass = pass_generator()
            output = subprocess.call(['vzctl', 'exec2', param[0], 'echo', 'root:'+param[4], '|', 'chpasswd'])
            if ( output != 0 ):
            #change dns key
                return self.err('pass '+str(output))
            output = subprocess.call(['vzctl', 'exec2', param[0], 'sed', '-i', '-e', '"s/123456/'+param[5]+'/"', '/usr/local/ispmgr/addon/lepusdns.pl'])
            if ( output != 0 ):
                return self.err('dns_error '+str(output))
            output = subprocess.call(['/etc/vz/shaper/gen.py'])
            self.response(output) # 0 = success

    def startServer(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        if ( output[2] != 'exist' ):
            return self.err("Err: cannot startServer, vzid does not exist")
        elif ( output[4] != 'down' ):
            return self.err("Err: cannot startServer, vzid already started")
        else:
            output = subprocess.call(['vzctl', 'start', param[0]])
            self.response(output) # 0 = success

    def stopServer(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        if ( output[2] != 'exist' ):
            return self.err("Err: cannot stopServer, vzid does not exist")
        elif ( output[4] != 'running' ):
            return self.err("Err: cannot stopServer, vzid already stopped")
        else:
            output = subprocess.call(['vzctl', 'stop', param[0]])
            self.response(output) # 0 = success

    def restartServer(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        if ( output[2] != 'exist' ):
            return self.err("Err: cannot restartServer, vzid does not exist")
        elif ( output[4] != 'running' ):
            return self.err("Err: cannot restartServer, vzid is not running")
        else:
            output = subprocess.call(['vzctl', 'restart', param[0]])
            self.response(output) # 0 = success

    def getStatus(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        self.response(output[4]) # running/down

    def getStatusEx(self, param):
        for char in param[0]:
            if char not in (string.digits): # [0-9]
                return self.err("incorrect id")
        output = subprocess.Popen(['vzctl', 'status', param[0]], stdout=subprocess.PIPE).communicate()[0].split()
        self.response(" ".join(output[2:])) # CTID N exist/deleted mounted/unmounted running/down

    def err(self, msg):
        self.send_response(403)
        self.send_header("Content-type", "text/plain")
        self.end_headers()
        self.wfile.write(msg)
        return msg

    def response(self, msg):
        self.send_response(200)
        self.send_header("Content-type", "text/plain")
        self.end_headers()
        self.wfile.write(msg)

    def do_GET(self):
        req = self.path[1:].split("/")

        if ( self.client_address[0] not in allowedIps ):
            return self.err("Err 0: ip not whitelisted")
        if ( len(req) < 3 ):
            return self.err("Err 1: not enough params")
        if ( req[0] != hashlib.md5(req[1]+secretKey).hexdigest() ):
            return self.err("Err 2: incorrect hash code")
        if ( req[1] not in dispatcher ):
            return self.err("Err 3: command not implemented")
        if ( len(req)-2 != dispatcher[req[1]] ):
            return self.err("Err 4: incorrect param count")
        if ( not req[2].isdigit() ):
            return self.err("Err 5: vzid is not numeric")

        errst = getattr(self, req[1])(req[2:])
        if ( errst == None ):
            self.AddLog(self.client_address[0], req[1:])
        else:
            self.AddErr(self.client_address[0], req[1:], errst)

    def AddLog(self, addr, msg):
        with open(logFile, "a") as myfile:
            msg.insert(0,addr)
            msg.insert(0,strftime("%Y-%m-%d %H:%M:%S", gmtime()))
            print str(msg)+"\n"
            myfile.write(str(msg)+"\n")

    def AddErr(self, addr, msg, err):
        with open(errlogFile, "a") as myfile:
            msg.insert(0,addr)
            msg.insert(0,strftime("%Y-%m-%d %H:%M:%S", gmtime()))
            msg.append(err)
            print str(msg)+"\n"
            myfile.write(str(msg)+"\n")


def pass_generator(size=10, chars=string.ascii_lowercase + string.ascii_uppercase + string.digits):
    return ''.join(random.choice(chars) for x in range(size))

class ThreadingHTTPServer(ThreadingMixIn, HTTPServer):
    pass


server = ThreadingHTTPServer((listen, port), ReqHandler)
server.serve_forever()

