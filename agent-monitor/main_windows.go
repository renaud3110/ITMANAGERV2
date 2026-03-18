//go:build windows

package main

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"time"

	"golang.org/x/sys/windows/svc"
	"golang.org/x/sys/windows/svc/eventlog"
	"golang.org/x/sys/windows/svc/mgr"
)

type monitorService struct {
	cfg    *Config
	logger *eventlog.Log
}

func (m *monitorService) Execute(args []string, r <-chan svc.ChangeRequest, changes chan<- svc.Status) (bool, uint32) {
	const cmdsAccepted = svc.AcceptStop | svc.AcceptShutdown
	changes <- svc.Status{State: svc.StartPending}
	changes <- svc.Status{State: svc.Running, Accepts: cmdsAccepted}

	go RunLoop(m.cfg)

	for c := range r {
		switch c.Cmd {
		case svc.Interrogate:
			changes <- c.CurrentStatus
		case svc.Stop, svc.Shutdown:
			changes <- svc.Status{State: svc.StopPending}
			return false, 0
		}
	}
	return false, 0
}

func installService(cfg *Config) error {
	exe, err := os.Executable()
	if err != nil {
		return err
	}
	exe, err = filepath.Abs(exe)
	if err != nil {
		return err
	}
	// Utilisation de mgr.CreateService (API Windows) au lieu de "sc create"
	// pour éviter les erreurs de syntaxe et 1060
	m, err := mgr.Connect()
	if err != nil {
		return fmt.Errorf("connexion au gestionnaire de services: %w", err)
	}
	defer m.Disconnect()

	s, err := m.CreateService("ITManagerMonitor", exe, mgr.Config{
		DisplayName:  "IT Manager Monitor",
		Description:  "Envoie les températures CPU/GPU et le statut en ligne vers IT Manager",
		StartType:    mgr.StartAutomatic,
		ErrorControl: mgr.ErrorNormal,
	}, []string{}...)
	if err != nil {
		return fmt.Errorf("création du service: %w", err)
	}
	defer s.Close()
	return nil
}

func uninstallService() error {
	exec.Command("sc", "stop", "ITManagerMonitor").Run()
	time.Sleep(2 * time.Second)
	cmd := exec.Command("sc", "delete", "ITManagerMonitor")
	return cmd.Run()
}

func main() {
	cfg, err := loadConfig()
	if err != nil {
		fmt.Fprintf(os.Stderr, "Erreur: %v\n", err)
		os.Exit(1)
	}

	isService, err := svc.IsWindowsService()
	if err != nil {
		fmt.Fprintf(os.Stderr, "IsWindowsService: %v\n", err)
		os.Exit(1)
	}

	if isService {
		elog, err := eventlog.Open("ITManagerMonitor")
		if err != nil {
			eventlog.InstallAsEventCreate("ITManagerMonitor", eventlog.Error|eventlog.Warning|eventlog.Info)
			elog, err = eventlog.Open("ITManagerMonitor")
		}
		if err == nil {
			defer elog.Close()
		}
		svc.Run("ITManagerMonitor", &monitorService{cfg: cfg, logger: elog})
		return
	}

	if len(os.Args) > 1 {
		switch os.Args[1] {
		case "install":
			if err := installService(cfg); err != nil {
				fmt.Fprintf(os.Stderr, "Installation: %v\n", err)
				os.Exit(1)
			}
			fmt.Println("Service installé. Démarrez avec: sc start ITManagerMonitor")
			return
		case "uninstall":
			if err := uninstallService(); err != nil {
				fmt.Fprintf(os.Stderr, "Désinstallation: %v\n", err)
				os.Exit(1)
			}
			fmt.Println("Service désinstallé.")
			return
		}
	}

	fmt.Println("IT Manager - Agent Moniteur (mode console)")
	fmt.Println("Appuyez sur Ctrl+C pour arrêter. Utilisez 'install' pour installer comme service.")
	fmt.Printf("Intervalle: %d secondes\n", cfg.Interval)
	RunLoop(cfg)
}
