open System
open System.IO
open System.Diagnostics
open System.Reflection
open Microsoft.Win32
open System.Security.Principal

[<EntryPoint>]
let main argv = 
    let programDir = Path.GetDirectoryName(Assembly.GetEntryAssembly().Location)

    let runElectron argument =
         let procStartInfo = 
            ProcessStartInfo(
                UseShellExecute = true,
                FileName = "node_modules\\electron\\dist\\electron.exe",
                Arguments = "javascript\\gui\\index.html " + argument,
                #if DEBUG
                WorkingDirectory = Directory.GetCurrentDirectory()
                #else
                WorkingDirectory = programDir
                #endif
            )
         let p = new Process(StartInfo = procStartInfo)
         p.Start() |> ignore
         true
    
    let rerunAsAdministrator argv = 
        (new Process(StartInfo = ProcessStartInfo(
                        UseShellExecute = true,
                        FileName = Assembly.GetEntryAssembly().Location,
                        Arguments = (argv |> String.concat " "),
                        Verb = "runas"
        ))).Start()

    let associate extension progID description icon application = 
        Registry.ClassesRoot.CreateSubKey(extension).SetValue("", progID)
        let key = Registry.ClassesRoot.CreateSubKey(progID)
        key.SetValue("", description)
        key.CreateSubKey("DefaultIcon").SetValue("", icon)
        key.CreateSubKey(@"Shell\Open\Command").SetValue("", application + " \"%1\"")
        true
 
    let unassociate extension = 
        Registry.ClassesRoot.DeleteSubKeyTree(extension)
        
    let runElectronWithArgv () = runElectron (argv |> Array.map (fun s -> "\"" + s + "\"") |> String.concat " ")
    
    match argv.Length with
        | 0 -> runElectron "" |> ignore
        | 1 -> 
            let isAdministrator = (new WindowsPrincipal(WindowsIdentity.GetCurrent())).IsInRole(WindowsBuiltInRole.Administrator)
                
            match argv.[0] with 
                | "assoc" when isAdministrator -> 
                    associate ".zba" "zblogcn.zba" "Z-Blog Packed App" (programDir + "\\resources\\Logo.ico") (Assembly.GetEntryAssembly().Location) |> ignore
                    associate ".gzba" "zblogcn.gzba" "Z-Blog Gzip Packed App" (programDir + "\\resources\\Logo.ico") (Assembly.GetEntryAssembly().Location) |> ignore
                | "unassoc" when isAdministrator ->
                    unassociate ".zba" |> ignore
                    unassociate ".gzba" |> ignore
                | ("assoc" | "unassoc") when not isAdministrator ->
                    rerunAsAdministrator argv |> ignore
                | _ -> runElectronWithArgv() |> ignore
        | _ -> runElectronWithArgv |> ignore
    
    0
