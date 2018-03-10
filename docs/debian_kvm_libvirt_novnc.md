

```
   KVM NODE                                    LEPUS NODE  
vnc websoket   [localhost]                  nginx https [0.0.0.0] ----- SSL ----- CLIENT (web browser)
      |                                              |
      |                                              |
      |                                              |
nginx wss proxy [0.0.0.0]  ----- SSL -----  novnc (html/ js) 

```
