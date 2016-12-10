package main                                                

import "io"
import "os"
import "log"
import "fmt"
import "bufio"
import "strings"
import "net/http"
import "io/ioutil"
import "encoding/hex"
import "crypto/sha256"
import "encoding/json"
import "github.com/gorilla/sessions"
import "github.com/kless/osutil/user/crypt/sha512_crypt"

var store = sessions.NewFilesystemStore("sess",[]byte("something-very-secret"))

type lepusMes struct {
    Err string
    Mes string
}

func main() {
	mux := http.NewServeMux()
	
	mux.HandleFunc("/", lepusMainPage)
	
	mux.HandleFunc("/api", lepusAPI)
	mux.HandleFunc("/api/login", lepusLoginAPI)
	mux.HandleFunc("/api/exit", lepusExitAPI)
	mux.HandleFunc("/api/test", lepusTestAPI)
	
	log.Println("Start server on port :8085")
	log.Fatal(http.ListenAndServe(":8085", mux))
}

func lepusMainPage(w http.ResponseWriter, r *http.Request) {
	file, _ := ioutil.ReadFile("/root/lepuscp/files/index.html")
	io.WriteString(w, string(file))
}


func lepusAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	session.Save(r, w)
	
	x := lepusAuth(w, r)
	
	if x ==false {
		w.Write([]byte("not auth"))
	}else{
		w.Write([]byte("hash: "+session.Values["hash"].(string)))
	}
}

func lepusLoginAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	
	ip := strings.Split(r.RemoteAddr,":")[0] 
	i := lepusLogin("root", "xxx")
	
	if i[0] == "right" {
		session.Values["user"] = "root"
		session.Values["hash"] = lepusSHA256(ip+i[1])
		session.Save(r, w)
		w.Write([]byte("sess: "+session.ID+"\nhash: "+session.Values["hash"].(string)+"\nip: "+ip))
	}else{
		w.Write([]byte("wrong login"))
	}
}

func lepusExitAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	session.Values["user"] = nil
	session.Values["hash"] = nil
	session.Save(r, w)
	w.Write([]byte("logout"))
}

func lepusTestAPI(w http.ResponseWriter, r *http.Request) {
	//ret := r.URL.Query()
	//fmt.Println(ret)
	// http://x.x.x.x:8085/api/test?id=123&name=test
	// map[id:[123] name:[test]]
	
	//r.ParseForm()
	//fmt.Println()
	//x1 := strings.Join(r.Form["login"], "")
	//x2 := strings.Join(r.Form["passwd"], "")
	
	m := lepusMes{"x2", "x"}
	b, _ := json.Marshal(m)
	
	w.Write([]byte(b))
}

func lepusLogin(user, passwd string) []string{
	x := lepusFindUser(user)
	if len(x) != 4 {
		fmt.Println("No hash passwd from /etc/shadow")
		os.Exit(0)
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
		return false
	}
	x := lepusLogin(session.Values["user"].(string), "no")	
	if session.Values["hash"].(string) == lepusSHA256(strings.Split(r.RemoteAddr,":")[0]+x[1]) {
		return true
	}else{
		return false
	}
}

func lepusSHA256(val string) string {
	h := sha256.New()
	h.Write([]byte(val))
	return hex.EncodeToString(h.Sum(nil))
}
