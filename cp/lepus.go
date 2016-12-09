package main                                                

import "os"
import "log"
import "fmt"
import "bufio"
import "strings"
import "net/http"
import "encoding/hex"
import "crypto/sha256"
import "github.com/gorilla/sessions"
import "github.com/kless/osutil/user/crypt/sha512_crypt"

var store = sessions.NewFilesystemStore("sess",[]byte("something-very-secret"))

func main() {
	mux := http.NewServeMux()
	
	mux.HandleFunc("/api", lepusAPI)
	
	log.Println("Start server on port :8085")
	log.Fatal(http.ListenAndServe(":8085", mux))
}

func lepusAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	session.Save(r, w)
	
	ip := strings.Split(r.RemoteAddr,":")[0] 
	i := lepusLogin("root", "xxx")
	
	if i[0] == "right" {
		h := sha256.New()
		h.Write([]byte(ip+i[1]))
		j := hex.EncodeToString(h.Sum(nil))

		session.Values["user"] = "root"
		session.Values["hash"] = j
		session.Save(r, w)
	}
	
	if session.Values["user"] != nil && session.Values["hash"] != nil {
		fmt.Println(session.Values["user"])
		fmt.Println(session.Values["hash"])
	}
	
	k := lepusAuth(ip, session.Values["user"].(string))
	
	w.Write([]byte(session.ID+" "+k+" "+ip))
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

func lepusAuth(ip, user string) string {
	x := lepusLogin(user, "no")
		
	h := sha256.New()
	h.Write([]byte(ip+x[1]))
	return hex.EncodeToString(h.Sum(nil))
}
