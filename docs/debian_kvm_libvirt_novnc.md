

```
   KVM NODE                                   LEPUS NODE  
vnc websoket   [localhost]                 nginx https [0.0.0.0]
      |                                             |
      |                                             |
      |                                             |
nginx wss proxy [0.0.0.0] ----- SSL ----- novnc (html/ js) ----- SSL ----- CLIENT (web browser)

```
