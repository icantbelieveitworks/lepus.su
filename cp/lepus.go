package main                                                

import "io"
import "os"
import "log"
import "fmt"
import "time"
import "bufio"
import "regexp"
import "strconv"
import "strings"
import "os/user"
import "os/exec"
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
	
	mux.HandleFunc("/", lepusPage)
	
	mux.HandleFunc("/api/login", lepusLoginAPI)
	mux.HandleFunc("/api/exit", lepusExitAPI)
	mux.HandleFunc("/api/get", lepusGetAPI)
	mux.HandleFunc("/api/test", lepusTestAPI)
	mux.HandleFunc("/api/addwebdir", lepusAddWebDirAPI)
	mux.HandleFunc("/api/delwebdir", lepusDelWebDirAPI)
	
	log.Println("Start server on port "+lepusConf["port"])
	log.Fatal(http.ListenAndServeTLS(lepusConf["port"], lepusConf["dir"]+"/server.crt", lepusConf["dir"]+"/server.key", mux))
}

func lepusPage(w http.ResponseWriter, r *http.Request) {
	ret := r.URL.Query()
	page := lepusConf["pages"]+"/index.html"
	switch strings.Join(ret["page"], "") {
	case "cp":
		page = lepusConf["pages"]+"/cp.html"
	}
	x := lepusAuth(w, r)
	if x != false && page == lepusConf["pages"]+"/index.html" {
		http.Redirect(w, r, "https://"+lepusConf["ip"]+lepusConf["port"]+"/?page=cp", 301)
		return 
	}
	if x == false && ret["page"] != nil {
		http.Redirect(w, r, "https://"+lepusConf["ip"]+lepusConf["port"], 301)
		return 
	}
	file, _ := ioutil.ReadFile(page)
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
	
	if strings.Join(r.Form["login"], "") != "lepus" { // only lepus can login (for this version)
		b := lepusMessage("Err", "Wrong login")
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
    //fmt.Println(new_hash+"\n"+hash)
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
				_, err := os.Readlink("/var/www/public/"+f.Name())
				if err == nil {
					continue
				}
				if dir.IsDir() {
					x += f.Name()+":"
				}
			}
			b = lepusMessage("OK", x)
	}
	w.Write(b)
}

func lepusAddWebDirAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	x := lepusAuth(w, r)
	if x == false {
		b := lepusMessage("Err", "Wrong auth")
		w.Write(b)
		return 
	}
	
	r.ParseForm()
	fmt.Println(r.Form)
	
	b := lepusMessage("Err", "Empty post")
	
	if strings.Join(r.Form["val"], "") != "" {
		re := regexp.MustCompile("^[a-z0-9.-]*$")
		if(re.MatchString(strings.Join(r.Form["val"], "")) == false){
			b = lepusMessage("Err", "Wrong website")
			w.Write(b)
			return
		}
		if _, err := os.Stat("/var/www/public/"+strings.Join(r.Form["val"], "")); os.IsNotExist(err) {
			a, _ :=user.Lookup(session.Values["user"].(string))
			uid, _ := strconv.Atoi(a.Uid)
			gid, _ := strconv.Atoi(a.Gid)
			os.Mkdir("/var/www/public/"+strings.Join(r.Form["val"], ""), 0755)
			os.Chown("/var/www/public/"+strings.Join(r.Form["val"], ""), uid, gid)
			
			if strings.Join(r.Form["symlink"], "") == "yes" {
				os.Symlink("/var/www/public/"+strings.Join(r.Form["val"], ""), "/var/www/public/www."+strings.Join(r.Form["val"], ""))
				// os.Chown("/var/www/public/symlink", 1000, 1000) not work for symlink
				// If the file is a symbolic link, it changes the uid and gid of the link's target.
				// https://golang.org/src/os/file_posix.go
				// so use exec
				//p := a.Uid+":"+a.Gid
				exec.Command("chown","-h", a.Uid+":"+a.Gid, "/var/www/public/www."+strings.Join(r.Form["val"], "")).Output()
			}
		}
		b = lepusMessage("OK", "Done")
	}
	
	w.Write(b)
}

func lepusDelWebDirAPI(w http.ResponseWriter, r *http.Request) {
	x := lepusAuth(w, r)
	if x == false {
		b := lepusMessage("Err", "Wrong auth")
		w.Write(b)
		return 
	}
	
	//x := "/var/www/public/"+strings.Join(r.Form["val"], "")
	i := "/var/www/public/test.ru"
	files, _ := ioutil.ReadDir("/var/www/public")
	for _, f := range files {		
		dir, _ := os.Stat("/var/www/public/"+f.Name())
		if dir.IsDir() {
			real, err := os.Readlink("/var/www/public/"+f.Name())
			if err != nil {
				continue
			}
			if i == real {
				fmt.Println("/var/www/public/"+f.Name()+" => "+real)
				os.RemoveAll("/var/www/public/"+f.Name())
			}
		}
	}
	//os.RemoveAll(i)
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
	
	// https://golang.org/src/os/user/user.go?s=684:820#L14
	
	//w.Write([]byte(x))
}
