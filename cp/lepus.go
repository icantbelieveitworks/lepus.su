package main

import "io"
import "os"
import "log"
import "fmt"
import "net"
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
	lepusConf["ip"] = lepusGetIP()
	lepusConf["dir"] = "/root/lepuscp"
	lepusConf["log"] = lepusConf["dir"] + "/logs/lepuscp.log"
	lepusConf["pages"] = lepusConf["dir"] + "/files"

	mux := http.NewServeMux()
	mux.HandleFunc("/", lepusPage)
	mux.HandleFunc("/api/login", lepusLoginAPI)
	mux.HandleFunc("/api/exit", lepusExitAPI)
	mux.HandleFunc("/api/get", lepusGetAPI)
	mux.HandleFunc("/api/test", lepusTestAPI)
	mux.HandleFunc("/api/addwebdir", lepusAddWebDirAPI)
	mux.HandleFunc("/api/delwebdir", lepusDelWebDirAPI)
	mux.HandleFunc("/api/chwebdir", lepusChWebDirAPI)
	mux.HandleFunc("/api/weblink", lepusAddWebLinkAPI)
	mux.HandleFunc("/api/chwebmode", lepusChWebModeAPI)

	log.Println("Start server on port " + lepusConf["port"])
	log.Fatal(http.ListenAndServeTLS(lepusConf["port"], lepusConf["dir"]+"/ssl/server.crt", lepusConf["dir"]+"/ssl/server.key", mux))
}

func lepusPage(w http.ResponseWriter, r *http.Request) {
	ret := r.URL.Query()
	page := lepusConf["pages"] + "/index.html"
	val := strings.Join(ret["page"], "")
	switch val {
	case "js":
		page = lepusConf["pages"] + "/lepus.js"
	case "cp":
		page = lepusConf["pages"] + "/cp.html"
	case "wwwedit":
		page = lepusConf["pages"] + "/wwwedit.html"
	}
	x := lepusAuth(w, r)
	if x != false && page == lepusConf["pages"]+"/index.html" {
		http.Redirect(w, r, "https://"+lepusConf["ip"]+lepusConf["port"]+"/?page=cp", 301)
		return
	}
	if x == false && ret["page"] != nil && val != "js" {
		http.Redirect(w, r, "https://"+lepusConf["ip"]+lepusConf["port"], 301)
		return
	}
	file, _ := ioutil.ReadFile(page)
	io.WriteString(w, string(file))
}

func lepusLoginAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	ip := strings.Split(r.RemoteAddr, ":")[0]
	r.ParseForm()
	fmt.Println(r.Form)
	if r.Form["login"] == nil || r.Form["passwd"] == nil {
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
		session.Values["hash"] = lepusSHA256(ip + i[1])
		session.Save(r, w)
		lepusLog("auth sucsess from ip " + ip + " user " + session.Values["user"].(string))
		fmt.Println("sess: " + session.ID + "\nhash: " + session.Values["hash"].(string) + "\nip: " + ip)
		b := lepusMessage("OK", "Sucsess login")
		w.Write(b)
	} else {
		lepusLog("auth error from ip " + ip)
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

func lepusLogin(user, passwd string) []string {
	x := lepusFindUser(user)
	if len(x) != 4 {
		fmt.Println("No hash passwd from /etc/shadow")
		return []string{"wrong", "no_hash"}
	}
	salt := "$" + x[1] + "$" + x[2] + "$"
	hash := salt + x[3]
	c := sha512_crypt.New()
	new_hash, _ := c.Generate([]byte(passwd), []byte(salt))
	if hash != new_hash {
		return []string{"wrong", hash}
	} else {
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
	if session.Values["hash"].(string) == lepusSHA256(strings.Split(r.RemoteAddr, ":")[0]+x[1]) {
		return true
	} else {
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
		file.WriteString(t.Format("[2006-01-02 15:04:05]") + " " + val + "\n")
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
	val := strings.Join(r.Form["val"], "")
	switch val {
	case "login":
		b = lepusMessage("OK", session.Values["user"].(string))

	case "type":
		site := strings.Join(r.Form["site"], "")
		if lepusRegexp(site, "") == false {
			b = lepusMessage("Err", "Wrong website")
			w.Write(b)
			return
		}
		b = lepusMessage("OK", "vhost")
		if _, err := os.Stat("/etc/apache2/sites-enabled/" + site + ".conf"); os.IsNotExist(err) {
			b = lepusMessage("OK", "mod_alias")
		}

	case "perm":
		site := strings.Join(r.Form["site"], "")
		if lepusRegexp(site, "") == false {
			b = lepusMessage("Err", "Wrong website")
			w.Write(b)
			return
		}
		i := lepusPathInfo("/var/www/public/" + site)
		b = lepusMessage("Err", "Not exist")
		if i["IsNotExist"] == 0 {
			b = lepusMessage("OK", "online")
			if i["Perm"] == 000 {
				b = lepusMessage("OK", "disable")
			}
		}

	case "www":
		ip := lepusGetIP()
		x := make(map[string]interface{})
		item := make(map[string]string)
		files, _ := ioutil.ReadDir("/var/www/public")
		fmt.Println(r.Form)
		for _, f := range files {

			key := f.Name()

			i := lepusPathInfo("/var/www/public/" + f.Name())
			if i["isDir"] == 0 || i["IsNotExist"] == 1 {
				continue
			}

			vh := "vhost"
			if _, err := os.Stat("/etc/apache2/sites-enabled/" + f.Name() + ".conf"); os.IsNotExist(err) {
				vh = "mod_alias"
			}

			fmt.Println(vh + " => " + f.Name())

			if r.Form["symlink"] != nil {
				if vh == "mod_alias" {
					path := "/var/www/public/" + strings.Join(r.Form["symlink"], "")
					real, _ := os.Readlink("/var/www/public/" + f.Name())
					fmt.Println("2")
					if real != path {
						fmt.Println(real + " != " + path)
						continue
					}
				} else {
					if strings.Join(r.Form["symlink"], "") != f.Name() {
						continue
					}
					file, _ := os.Open("/etc/apache2/sites-enabled/" + f.Name() + ".conf")
					defer file.Close()

					scanner := bufio.NewScanner(file)

					for scanner.Scan() {
						o := strings.Split(scanner.Text(), " ")
						if o[0] == "ServerAlias" {
							key = strings.Join(o, " ")
						}
					}
				}
			} else if i["Readlink"] == 1 {
				fmt.Println("1")
				continue
			}
			item["ip"] = ip
			item["http"] = vh
			if i["Perm"] != 000 {
				item["status"] = "online"
			} else {
				item["status"] = "disable"
			}
			x[key] = item
			item = make(map[string]string)
		}
		z, _ := json.Marshal(x)
		b = lepusMessage("OK", string(z))
	}
	w.Write(b)
}

func lepusAddWebLinkAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	x := lepusAuth(w, r)
	if x == false {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	if r.Form["val"] == nil || r.Form["link"] == nil || r.Form["command"] == nil {
		w.Write(lepusMessage("Err", "Empty post"))
		return
	}
	val := strings.Join(r.Form["val"], "")
	link := strings.Join(r.Form["link"], "")
	command := strings.Join(r.Form["command"], "")
	if lepusRegexp(val, "") == false || lepusRegexp(link, "") == false || lepusRegexp(command, "") == false {
		w.Write(lepusMessage("Err", "Wrong data"))
		return
	}
	mode := lepusGetTypeWWW(val)
	switch mode {
	case "mod_alias":
		pathSite := "/var/www/public/" + val
		pathLink := "/var/www/public/" + link
		if command == "add" {
			i := lepusPathInfo(pathSite)
			if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
				w.Write(lepusMessage("Err", "Site is not exist"))
				return
			}
			i = lepusPathInfo(pathLink)
			if i["IsNotExist"] == 0 {
				w.Write(lepusMessage("Err", "Link already exist"))
				return
			}
			a, _ := user.Lookup(session.Values["user"].(string))
			os.Symlink(pathSite, pathLink)
			exec.Command("chown", "-h", a.Uid+":"+a.Gid, pathLink).Output()
		}
		if command == "del" {
			i := lepusPathInfo(pathSite)
			if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
				w.Write(lepusMessage("Err", "Main dir not found"))
				return
			}
			i = lepusPathInfo(pathLink)
			if i["Readlink"] == 0 || i["isDir"] == 0 || i["IsNotExist"] == 1 {
				w.Write(lepusMessage("Err", "Link dir not found"))
				return
			}
			pathReal, _ := os.Readlink(pathLink)
			if pathReal != pathSite {
				w.Write(lepusMessage("Err", "Link != Dir"))
				return
			}
			fmt.Println(pathLink + " => " + pathReal)
			os.RemoveAll(pathLink)
		}
		w.Write(lepusMessage("OK", "Done"))
	case "vhost":
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		// lepusGetTypeWWW already check lepusPathInfo
		if command == "add" {
			w.Write(lepusApacheAlias("add", link, confPath))
		} else {
			w.Write(lepusApacheAlias("del", link, confPath))
		}
	}
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
	if r.Form["val"] == nil || r.Form["mode"] == nil {
		w.Write(lepusMessage("Err", "Empty post"))
		return
	}
	val := strings.Join(r.Form["val"], "")
	mode := strings.Join(r.Form["mode"], "")
	if lepusRegexp(val, "") == false {
		w.Write(lepusMessage("Err", "Wrong data"))
		return
	}
	if mode == "vhost" {
		config, _ := ioutil.ReadFile("/root/lepuscp/files/apache.tmp")
		x := strings.NewReplacer("%domain%", val)
		result := x.Replace(string(config))
		path := "/etc/apache2/sites-enabled/" + val + ".conf"
		i := lepusPathInfo(path)
		if i["IsNotExist"] == 0 {
			w.Write(lepusMessage("Err", "Dir exist"))
			return
		}
		os.Create(path)
		if _, err := os.Stat(path); err == nil {
			file, _ := os.OpenFile(path, os.O_RDWR, 0755)
			defer file.Close()
			file.WriteString(result)
			file.Sync()
		}
		lepusApache("reload")
	}
	path := "/var/www/public/" + val
	i := lepusPathInfo(path)
	if i["IsNotExist"] == 0 {
		w.Write(lepusMessage("Err", "Dir exist"))
		return
	}
	a, _ := user.Lookup(session.Values["user"].(string))
	uid, _ := strconv.Atoi(a.Uid)
	gid, _ := strconv.Atoi(a.Gid)
	os.Mkdir(path, 0755)
	os.Chown(path, uid, gid)
	w.Write(lepusMessage("OK", lepusGetIP()))
}

func lepusDelWebDirAPI(w http.ResponseWriter, r *http.Request) {
	x := lepusAuth(w, r)
	if x == false {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	if r.Form["val"] == nil {
		w.Write(lepusMessage("Err", "Empty post"))
		return
	}
	val := strings.Join(r.Form["val"], "")
	if lepusRegexp(val, "") == false {
		w.Write(lepusMessage("Err", "Wrong website"))
		return
	}
	mode := lepusGetTypeWWW(val)
	if mode == "vhost" {
		// no need check lepusPathInfo => already check it (in lepusGetTypeWWW) => conf file exist, not dir and not link.
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		os.RemoveAll(confPath)
		leConfPath := "/etc/apache2/sites-enabled/" + val + "-le-ssl.conf"
		i := lepusPathInfo(leConfPath)
		if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
			os.RemoveAll(leConfPath)
		}
		lepusApache("reload")
	}
	pathSite := "/var/www/public/" + val
	i := lepusPathInfo(pathSite)
	if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
		w.Write(lepusMessage("Err", "Not found"))
		return
	}
	files, _ := ioutil.ReadDir("/var/www/public")
	for _, f := range files {
		i = lepusPathInfo("/var/www/public/" + f.Name())
		if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 0 || lepusRegexp(f.Name(), "") == false {
			continue
		}
		pathReal, _ := os.Readlink("/var/www/public/" + f.Name())
		if pathReal == pathSite {
			fmt.Println("/var/www/public/" + f.Name() + " => " + pathReal)
			os.RemoveAll("/var/www/public/" + f.Name())
		}
	}
	os.RemoveAll(pathSite)
	w.Write(lepusMessage("OK", "Done"))
}

func lepusApacheAlias(command, alias, confPath string) []byte {
	val := ""
	new := ""
	val2 := ""
	file, _ := os.Open(confPath)
	defer file.Close()
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		o := strings.Split(scanner.Text(), " ")
		if o[0] == "ServerAlias" {
			val = strings.Join(o, " ")
			if stringInSlice(alias, o) && command == "add" {
				return lepusMessage("Err", "Domain already add")
			}
		}
		if o[0] == "ServerName" {
			val2 = strings.Join(o, " ")
		}
	}
	switch command {
	case "add":
		if val == "" {
			val = val2
			new = val + "\nServerAlias " + alias
		} else {
			new = val + " " + alias
			regex, _ := regexp.Compile("\\s+")
			new = regex.ReplaceAllString(new, " ")
		}
	case "del":
		new = strings.Replace(val, alias, "", -1)
		if strings.Trim(new, " ") == "ServerAlias" {
			new = ""
		}
	}
	if val != "" {
		text, _ := ioutil.ReadFile(confPath)
		regex, _ := regexp.Compile("\n\n")
		result := regex.ReplaceAllString(strings.Replace(string(text), val, new, -1), "\n")
		ioutil.WriteFile(confPath, []byte(result), 0755)
	}
	lepusApache("reload")
	return lepusMessage("OK", "Done")
}

func lepusGetIP() string {
	x := "0.0.0.0"
	addrs, _ := net.InterfaceAddrs()
	for _, a := range addrs {
		if ipnet, ok := a.(*net.IPNet); ok && !ipnet.IP.IsLoopback() {
			if ipnet.IP.To4() != nil {
				x = ipnet.IP.String()
				break
			}
		}
	}
	return x
}

func lepusChWebDirAPI(w http.ResponseWriter, r *http.Request) {
	x := lepusAuth(w, r)
	if x == false {
		b := lepusMessage("Err", "Wrong auth")
		w.Write(b)
		return
	}
	r.ParseForm()
	b := lepusMessage("Err", "Empty post")
	val := strings.Join(r.Form["val"], "")
	if lepusRegexp(val, "") == false {
		b = lepusMessage("Err", "Wrong website")
		w.Write(b)
		return
	}
	path := "/var/www/public/" + val
	i := lepusPathInfo(path)
	if i["IsNotExist"] == 1 {
		b = lepusMessage("Err", "Not found")
		w.Write(b)
		return
	}
	if i["Readlink"] == 1 {
		b = lepusMessage("Err", "It`s symlink")
		w.Write(b)
		return
	}
	b = lepusMessage("Err", "It isn`t dir")
	if i["isDir"] == 1 {
		if i["Perm"] == 000 {
			os.Chmod(path, 0755)
			b = lepusMessage("OK", "online")
		} else {
			os.Chmod(path, 0000)
			b = lepusMessage("OK", "disable")
		}
	}
	w.Write(b)
}

func lepusRegexp(data, val string) bool {
	re := regexp.MustCompile("^[a-z0-9._-]*$")
	switch val {
	case "09":
		re = regexp.MustCompile("^[0-9]*$")
	case "az":
		re = regexp.MustCompile("^[a-z]*$")
	}
	return re.MatchString(data)
}

func lepusPathInfo(val string) map[string]int {
	info := make(map[string]int)
	info["IsNotExist"] = 0
	info["isDir"] = 0
	info["Readlink"] = 0
	dir, err := os.Stat(val)
	if os.IsNotExist(err) {
		info["IsNotExist"] = 1
	} else {
		if dir.IsDir() {
			info["isDir"] = 1
			mode := dir.Mode()
			j := mode.String()
			x := fmt.Sprintf("%d%d%d", lepusPermToInt(j[1:4]), lepusPermToInt(j[4:7]), lepusPermToInt(j[7:10]))
			info["Perm"], _ = strconv.Atoi(x)
		}
		_, err = os.Readlink(val)
		if err == nil {
			info["Readlink"] = 1
		}
	}
	return info
}

func lepusPermToInt(val string) int {
	x := 0
	switch val {
	case "rwx":
		x = 7
	case "rw-":
		x = 6
	case "r-x":
		x = 5
	case "r--":
		x = 4
	case "-wx":
		x = 3
	case "-w-":
		x = 2
	case "--x":
		x = 1
	}
	return x
}

func lepusGetTypeWWW(val string) string {
	i := lepusPathInfo("/etc/apache2/sites-enabled/" + val + ".conf")
	if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
		return "vhost"
	}
	return "mod_alias"
}

func stringInSlice(a string, list []string) bool {
	for _, b := range list {
		if b == a {
			return true
		}
	}
	return false
}

func lepusChWebModeAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	x := lepusAuth(w, r)
	if x == false {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	if r.Form["val"] == nil || r.Form["mode"] == nil {
		w.Write(lepusMessage("Err", "Empty post"))
		return
	}
	val := strings.Join(r.Form["val"], "")
	mode := strings.Join(r.Form["mode"], "")
	if lepusRegexp(val, "") == false || lepusRegexp(mode, "") == false {
		w.Write(lepusMessage("Err", "Wrong website"))
		return
	}

	if mode == lepusGetTypeWWW(val) {
		w.Write(lepusMessage("Err", "Same mode"))
		return
	}
	pathSite := "/var/www/public/" + val
	i := lepusPathInfo(pathSite)
	if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
		w.Write(lepusMessage("Err", "Not found"))
		return
	}
	switch mode {
	case "mod_alias":
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		str := ""
		file, _ := os.Open(confPath)
		defer file.Close()
		scanner := bufio.NewScanner(file)
		for scanner.Scan() {
			o := strings.Split(scanner.Text(), " ")
			if o[0] == "ServerAlias" {
				str = strings.Join(o, " ")
			}
		}
		os.RemoveAll(confPath)
		if str != "" {
			regex, _ := regexp.Compile("\\s+")
			str = regex.ReplaceAllString(str, " ")
			result := strings.Split(str, " ")
			for key := range result {
				if result[key] == "" || result[key] == "ServerAlias" || lepusRegexp(result[key], "") == false {
					continue
				}
				pathLink := "/var/www/public/" + result[key]
				i := lepusPathInfo(pathLink)
				if i["IsNotExist"] == 0 {
					continue
				}
				a, _ := user.Lookup(session.Values["user"].(string))
				os.Symlink(pathSite, pathLink)
				exec.Command("chown", "-h", a.Uid+":"+a.Gid, pathLink).Output()
			}
		}
		leConfPath := "/etc/apache2/sites-enabled/" + val + "-le-ssl.conf"
		i := lepusPathInfo(leConfPath)
		if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
			os.RemoveAll(leConfPath)
		}

	case "vhost":
		config, _ := ioutil.ReadFile("/root/lepuscp/files/apache.tmp")
		x := strings.NewReplacer("%domain%", val)
		result := x.Replace(string(config))
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		i = lepusPathInfo(confPath)
		if i["IsNotExist"] == 0 {
			w.Write(lepusMessage("Err", "Dir exist"))
			return
		}
		os.Create(confPath)
		if _, err := os.Stat(confPath); err == nil {
			file, _ := os.OpenFile(confPath, os.O_RDWR, 0755)
			defer file.Close()
			file.WriteString(result)
			file.Sync()
		}
		files, _ := ioutil.ReadDir("/var/www/public")
		for _, f := range files {
			i = lepusPathInfo("/var/www/public/" + f.Name())
			if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 0 || lepusRegexp(f.Name(), "") == false {
				continue
			}
			pathReal, _ := os.Readlink("/var/www/public/" + f.Name())
			if pathReal == pathSite {
				lepusApacheAlias("add", f.Name(), confPath)
				os.RemoveAll("/var/www/public/" + f.Name())
			}
		}
	}
	lepusApache("reload")
	w.Write(lepusMessage("OK", "Done"))
}

func lepusApache(cmd string) {
	switch cmd {
	case "start":
		exec.Command("/etc/init.d/apache2", "start").Output()

	case "stop":
		exec.Command("/etc/init.d/apache2", "stop").Output()

	case "restart":
		exec.Command("/etc/init.d/apache2", "restart").Output()

	default:
		exec.Command("/etc/init.d/apache2", "reload").Output()
	}
}

func lepusTestAPI(w http.ResponseWriter, r *http.Request) {
	w.Write([]byte("test"))
}
