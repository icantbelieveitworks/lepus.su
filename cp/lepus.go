package main                                                

import "io"
import "os"
import "log"
import "fmt"
import "time"
import "bufio"
import "strings"
import "net/http"
import "io/ioutil"
import "encoding/hex"
import "crypto/sha256"
import "encoding/json"
import "github.com/gorilla/sessions"
import "github.com/kless/osutil/user/crypt/sha512_crypt"

var lepusConf = make(map[string]string)
var store = sessions.NewFilesystemStore("sess", []byte("something-very-secret"))

type lepusMes struct {
    Err string
    Mes string
}

func main() {
	lepusConf["port"] = ":8085"
	lepusConf["ip"] = "151.80.209.161"
	lepusConf["dir"] = "/root/lepuscp"
	lepusConf["log"] = lepusConf["dir"]+"/lepuscp.log"
	lepusConf["pages"] = lepusConf["dir"]+"/files"
	
	mux := http.NewServeMux()
	
	mux.HandleFunc("/", lepusMainPage)
	mux.HandleFunc("/page/cp", lepusControlPage)
	
	mux.HandleFunc("/api/login", lepusLoginAPI)
	mux.HandleFunc("/api/exit", lepusExitAPI)
	mux.HandleFunc("/api/get", lepusGetAPI)
	mux.HandleFunc("/api/test", lepusTestAPI)
	
	log.Println("Start server on port "+lepusConf["port"])
	log.Fatal(http.ListenAndServeTLS(lepusConf["port"], lepusConf["dir"]+"/server.crt", lepusConf["dir"]+"/server.key", mux))
}

func lepusMainPage(w http.ResponseWriter, r *http.Request) {
	x := lepusAuth(w, r)
	if x != false {
		http.Redirect(w, r, "https://"+lepusConf["ip"]+lepusConf["port"]+"/page/cp", 301)
		return 
	}
	file, _ := ioutil.ReadFile(lepusConf["pages"]+"/index.html")
	io.WriteString(w, string(file))
}

func lepusControlPage(w http.ResponseWriter, r *http.Request) {
	x := lepusAuth(w, r)
	if x == false {
		http.Redirect(w, r, "https://"+lepusConf["ip"]+lepusConf["port"], 301)
		return 
	}
	
	file, _ := ioutil.ReadFile(lepusConf["pages"]+"/cp.html")
	io.WriteString(w, string(file))
}

func lepusLoginAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	ip := strings.Split(r.RemoteAddr,":")[0]
	
	r.ParseForm()
	fmt.Println(r.Form)
	if r.Form["login"] == nil || r.Form["passwd"] == nil{
		b := lepusMessage("Err", "empty post")
		w.Write(b)
		return
	}
	
	i := lepusLogin(strings.Join(r.Form["login"], ""), strings.Join(r.Form["passwd"], ""))
	
	if i[0] == "right" {
		session.Values["user"] = strings.Join(r.Form["login"], "")
		session.Values["hash"] = lepusSHA256(ip+i[1])
		session.Save(r, w)
		fmt.Println("sess: "+session.ID+"\nhash: "+session.Values["hash"].(string)+"\nip: "+ip)
		b := lepusMessage("OK", "Sucsess login")
		w.Write(b)
	}else{
		lepusLog("auth error from ip "+ip)
		b := lepusMessage("Err", "Wrong login or passwd")
		w.Write(b)
	}
}

func lepusExitAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	session.Values["user"] = nil
	session.Values["hash"] = nil
	session.Save(r, w)
}

func lepusLogin(user, passwd string) []string{
	x := lepusFindUser(user)
	if len(x) != 4 {
		fmt.Println("No hash passwd from /etc/shadow")
		return []string{"wrong", "no_hash"}
	}
	
	salt := "$"+x[1]+"$"+x[2]+"$"
	hash := salt+x[3]
	
	c := sha512_crypt.New()
    new_hash, _ := c.Generate([]byte(passwd), []byte(salt))
    fmt.Println(new_hash+"\n"+hash)
    if hash != new_hash {
		return []string{"wrong", hash} 
	}else{
		return []string{"right", hash} 
	}
}

func lepusFindUser(user string) []string {
	var userdata []string
	file, _ := os.Open("/etc/shadow")
	defer file.Close()
	
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		x := strings.Split(scanner.Text(), ":")		
		if x[0] == user {	
			userdata = strings.Split(x[1], "$")
			break
        }
    }
    return userdata
}

func lepusAuth(w http.ResponseWriter, r *http.Request) bool {
	session, _ := store.Get(r, "lepuscp")
	if session.Values["user"] == nil || session.Values["hash"] == nil {
		lepusExitAPI(w, r)
		return false
	}
	x := lepusLogin(session.Values["user"].(string), "no")	
	if session.Values["hash"].(string) == lepusSHA256(strings.Split(r.RemoteAddr,":")[0]+x[1]) {
		return true
	}else{
		lepusExitAPI(w, r)
		return false
	}
}

func lepusSHA256(val string) string {
	h := sha256.New()
	h.Write([]byte(val))
	return hex.EncodeToString(h.Sum(nil))
}

func lepusMessage(Err, Mes string) []byte {
	m := lepusMes{Err, Mes}
	b, _ := json.Marshal(m)
	return b
}

func lepusLog(val string) {
	t := time.Now()
	if _, err := os.Stat(lepusConf["log"]); os.IsNotExist(err) {
		os.Create(lepusConf["log"])
	}
	if _, err := os.Stat(lepusConf["log"]); err == nil {
		file, _ := os.OpenFile(lepusConf["log"], os.O_APPEND|os.O_RDWR, 0644)
		defer file.Close()
		file.WriteString(t.Format("[2006-01-02 15:04:05]")+" "+val+"\n")
		file.Sync()
	}
}

func lepusGetAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	x := lepusAuth(w, r)
	if x == false {
		b := lepusMessage("Err", "Wrong auth")
		w.Write(b)
		return 
	}
	
	r.ParseForm()
	fmt.Println(r.Form)
	
	b := lepusMessage("Err", "empty post")	
	switch strings.Join(r.Form["val"], "") {
		case "login":
			b = lepusMessage("OK", session.Values["user"].(string))
			
		case "wwwlist":
			x := ""
			files, _ := ioutil.ReadDir("/var/www/public")
				for _, f := range files {
				dir, _ := os.Stat("/var/www/public/"+f.Name())
				if dir.IsDir() {
					x += f.Name()+":"
				}
			}
			b = lepusMessage("OK", x)
	}
	w.Write(b)
}

func lepusTestAPI(w http.ResponseWriter, r *http.Request) {
	//ret := r.URL.Query()
	//fmt.Println(ret)
	// http://x.x.x.x:8085/api/test?id=123&name=test
	// map[id:[123] name:[test]]
	
	//r.ParseForm()
	//fmt.Println(r.Form)
	//x1 := strings.Join(r.Form["login"], "")
	//x2 := strings.Join(r.Form["passwd"], "")
	//fmt.Println(x1)
	//fmt.Println(x2)
	
	//lepusLog("test!")
	//b := lepusMessage("err", "123123123")
	
	w.Write([]byte("test"))
}
